#!/usr/bin/env python
# -*- coding: utf-8 -*-

import sys
import json
import time
import os
from datetime import datetime
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.chrome.service import Service
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.common.exceptions import TimeoutException, NoSuchElementException
from webdriver_manager.chrome import ChromeDriverManager
from Sastrawi.Stemmer.StemmerFactory import StemmerFactory
from Sastrawi.StopWordRemover.StopWordRemoverFactory import StopWordRemoverFactory
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.metrics.pairwise import cosine_similarity
from googletrans import Translator

os.environ['PYTHONIOENCODING'] = 'utf-8'

def crawl_google_scholar(author_name, keyword, max_results=5):
    chrome_options = Options()
    chrome_options.add_argument('--headless')
    chrome_options.add_argument('--no-sandbox')
    chrome_options.add_argument('--disable-dev-shm-usage')
    chrome_options.add_argument('--disable-gpu')
    chrome_options.add_argument('--window-size=1920,1080')
    chrome_options.add_argument('--user-agent=Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/119.0.0.0 Safari/537.36')
    
    factory_stem = StemmerFactory()
    stemmer = factory_stem.create_stemmer()
    
    factory_stop = StopWordRemoverFactory()
    stopword = factory_stop.create_stop_word_remover()
    
    translator = Translator()
    try:
        # STEP 1: Initialize Chrome Driver
        from webdriver_manager.core.os_manager import ChromeType
        chrome_driver_path = ChromeDriverManager(chrome_type=ChromeType.GOOGLE).install()
        
        import os
        if not chrome_driver_path.endswith('.exe'):
            driver_dir = os.path.dirname(chrome_driver_path)
            chrome_driver_path = os.path.join(driver_dir, 'chromedriver.exe')
            if not os.path.exists(chrome_driver_path):
                driver_dir = os.path.dirname(driver_dir)
                chrome_driver_path = os.path.join(driver_dir, 'chromedriver.exe')
        
        if not os.path.exists(chrome_driver_path):
            raise Exception(f"ChromeDriver not found at {chrome_driver_path}")
        
        driver = webdriver.Chrome(
            service=Service(chrome_driver_path),
            options=chrome_options
        )
        
        results = []
        
        # ==================== START: CRAWL GOOGLE SCHOLAR ====================
        # STEP 2: Search for Author in Google Scholar
        search_url = f"https://scholar.google.com/scholar?hl=en&as_sdt=0%2C5&q={author_name.replace(' ', '+')}&btnG="
        driver.get(search_url)
        time.sleep(3)
        
        # STEP 3: Find and Open Author Profile
        try:
            author_links = driver.find_elements(By.CSS_SELECTOR, "a[href*='/citations?user=']")
            
            if not author_links:
                print("[X] ERROR: No author profile found!")
                print(f"  Author searched: '{author_name}'")
                driver.quit()
                return []
            
            author_profile_url = author_links[0].get_attribute('href')
            author_display_name = author_links[0].text
            
            driver.get(author_profile_url)
            time.sleep(3)
            
        except Exception as e:
            print(f"[X] ERROR finding author profile: {str(e)}")
            driver.quit()
            return []
        
        # STEP 4: Get Publications List from Author Profile
        publications = driver.find_elements(By.CSS_SELECTOR, "tr.gsc_a_tr")
        
        if len(publications) == 0:
            print("[X] ERROR: No publications found on profile!")
            driver.quit()
            return []
        
        count = 0
        # STEP 5: Loop Through Publications and Extract Data
        for pub in publications:
            if count >= max_results:
                break
            
            try:
                # STEP 5.1: Extract Basic Publication Info
                title_elem = pub.find_element(By.CSS_SELECTOR, "a.gsc_a_at")
                title = title_elem.text
                
                # ==================== START: PREPROCESSING ====================
                try:
                    translated_title = translator.translate(title, src='en', dest='id').text
                except:
                    translated_title = title
                
                stemmed_title = stemmer.stem(translated_title)
                clean_title = stopword.remove(stemmed_title)

                try:
                    translated_keyword = translator.translate(keyword, src='en', dest='id').text
                except:
                    translated_keyword = keyword
                
                clean_keyword = stopword.remove(stemmer.stem(translated_keyword.lower()))
                # ==================== END: PREPROCESSING ====================

                # ==================== START: COMPUTE SIMILARITY ====================
                similarity_score = compute_tfidf_similarity(
                    clean_keyword,
                    clean_title.lower()
                )
                # ==================== END: COMPUTE SIMILARITY ====================
                
                # STEP 5.2: Extract Article Link and Metadata
                article_link = title_elem.get_attribute('data-href')

                if not article_link:
                    article_link = title_elem.get_attribute('href')
                
                keyword_words = keyword.lower().split()
                title_lower = title.lower()
                keyword_match = any(word in title_lower for word in keyword_words)
                
                authors_elem = pub.find_element(By.CSS_SELECTOR, "div.gs_gray")
                authors = authors_elem.text
                
                try:
                    journal_elem = pub.find_elements(By.CSS_SELECTOR, "div.gs_gray")[1]
                    journal = journal_elem.text
                except:
                    journal = "N/A"
                
                try:
                    year_elem = pub.find_element(By.CSS_SELECTOR, "span.gsc_a_h")
                    year = year_elem.text
                except:
                    year = "N/A"
                
                try:
                    citations_elem = pub.find_element(By.CSS_SELECTOR, "a.gsc_a_ac")
                    citations = citations_elem.text if citations_elem.text else "0"
                except:
                    citations = "0"
                full_link = "N/A"
                publish_date = "N/A"
                
                # STEP 5.3: Open Article Detail Page for Additional Info
                try:
                    if article_link:
                        if article_link.startswith('http'):
                            detail_url = article_link
                        else:
                            detail_url = f"https://scholar.google.com{article_link}"
                        
                        driver.execute_script("window.open('');")
                        driver.switch_to.window(driver.window_handles[1])
                        driver.get(detail_url)
                        time.sleep(3)
                        
                        # STEP 5.4: Extract Journal Link
                        try:
                            title_area = driver.find_element(By.ID, "gsc_oci_title_gg")
                            all_links = title_area.find_elements(By.TAG_NAME, "a")
                            if all_links:
                                full_link = all_links[0].get_attribute('href')
                        except:
                            pass
                        
                        # STEP 5.5: Extract Publication Date
                        try:
                            rows = driver.find_elements(By.CSS_SELECTOR, "div.gs_scl")
                            if len(rows) == 0:
                                rows = driver.find_elements(By.CSS_SELECTOR, "div.gsc_oci_field")
                            
                            for row in rows:
                                try:
                                    field = row.find_element(By.CSS_SELECTOR, "div.gsc_oci_field")
                                    value = row.find_element(By.CSS_SELECTOR, "div.gsc_oci_value")
                                    field_text = field.text.strip()
                                    value_text = value.text.strip()
                                    if 'Publication date' in field_text or field_text == 'Date' or 'date' in field_text.lower():
                                        publish_date = value_text
                                        break
                                except:
                                    continue
                            
                            if publish_date == "N/A":
                                publish_date = year
                        except:
                            publish_date = year
                        
                        driver.close()
                        driver.switch_to.window(driver.window_handles[0])
                        
                except:
                    if len(driver.window_handles) > 1:
                        driver.close()
                        driver.switch_to.window(driver.window_handles[0])
                
                # STEP 5.6: Build Result Object
                result = {
                    'no': count + 1,
                    'title': title,
                    'clean_title': clean_title,
                    'authors': authors,
                    'journal': journal,
                    'year': year,
                    'publish_date': publish_date,
                    'citations': citations,
                    'link': full_link,
                    'keyword_match': 'Yes' if keyword_match else 'No',
                    'tfidf_similarity': similarity_score
                }
                
                results.append(result)
                count += 1
                
            except:
                continue
        # ==================== END: CRAWL GOOGLE SCHOLAR ====================
        
        driver.quit()
        results = sorted(results, key=lambda x: x['tfidf_similarity'], reverse=True)
        return results
        
    except Exception as e:
        print(f"Error: {str(e)}")
        if 'driver' in locals():
            driver.quit()
        return []

def compute_tfidf_similarity(keyword, document):
    try:
        if not keyword.strip() or not document.strip():
            return 0.0
        
        corpus = [keyword, document]
        vectorizer = TfidfVectorizer(
            ngram_range=(1, 2),
            lowercase=True,
            token_pattern=r'(?u)\b\w+\b'
        )
        tfidf_matrix = vectorizer.fit_transform(corpus)
        similarity = cosine_similarity(tfidf_matrix[0:1], tfidf_matrix[1:2])[0][0]
        return round(float(similarity), 4)
    except:
        return 0.0
    
def main():
    if len(sys.argv) < 4:
        print("Usage: python scholar_crawler.py <author_name> <keyword> <max_results>")
        sys.exit(1)
    
    author_name = sys.argv[1]
    keyword = sys.argv[2]
    max_results = int(sys.argv[3])
    
    print("="*60)
    print("Google Scholar Crawler")
    print("="*60)
    print(f"Author: {author_name}")
    print(f"Keyword: {keyword}")
    print(f"Max results: {max_results}")
    print("="*60)
    print()
    
    results = crawl_google_scholar(author_name, keyword, max_results)
    
    output = {
        'search_params': {
            'author': author_name,
            'keyword': keyword,
            'max_results': max_results,
            'timestamp': datetime.now().strftime('%Y-%m-%d %H:%M:%S')
        },
        'total_found': len(results),
        'results': results
    }
    
    output_file = 'results.json'
    with open(output_file, 'w', encoding='utf-8') as f:
        json.dump(output, f, ensure_ascii=False, indent=2)
    
    print(f"\nResults saved to {output_file}")
    print("Success!")

if __name__ == "__main__":
    main()
