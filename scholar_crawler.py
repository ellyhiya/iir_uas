#!/usr/bin/env python
# -*- coding: utf-8 -*-
"""
Google Scholar Crawler using Selenium
Crawl articles based on author name and keyword
"""

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

# Set environment for better encoding support
os.environ['PYTHONIOENCODING'] = 'utf-8'

def crawl_google_scholar(author_name, keyword, max_results=5):
    """
    Crawl Google Scholar for articles by author and keyword
    
    Args:
        author_name (str): Name of the author
        keyword (str): Keyword to search in articles
        max_results (int): Maximum number of results to return
    
    Returns:
        list: List of dictionaries containing article information
    """
    
    # Setup Chrome options
    chrome_options = Options()
    chrome_options.add_argument('--headless')  # Run without opening browser window
    chrome_options.add_argument('--no-sandbox')
    chrome_options.add_argument('--disable-dev-shm-usage')
    chrome_options.add_argument('--disable-gpu')
    chrome_options.add_argument('--window-size=1920,1080')
    chrome_options.add_argument('--user-agent=Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/119.0.0.0 Safari/537.36')
    
    print("="*60)
    print("STEP 1: Initializing Chrome driver...")
    print("="*60)
    print("[INIT] Initializing Sastrawi Stemmer and Stopword Remover...")
    factory_stem = StemmerFactory()
    stemmer = factory_stem.create_stemmer()
    
    factory_stop = StopWordRemoverFactory()
    stopword = factory_stop.create_stop_word_remover()
    
    print("[INIT] Initializing Google Translator...")
    translator = Translator()
    try:
        # Initialize driver with webdriver-manager
        # Install ChromeDriver and get the correct executable path
        from webdriver_manager.core.os_manager import ChromeType
        chrome_driver_path = ChromeDriverManager(chrome_type=ChromeType.GOOGLE).install()
        
        # Fix path if it points to a non-executable file
        import os
        if not chrome_driver_path.endswith('.exe'):
            # Find the actual chromedriver.exe in the same directory
            driver_dir = os.path.dirname(chrome_driver_path)
            chrome_driver_path = os.path.join(driver_dir, 'chromedriver.exe')
            if not os.path.exists(chrome_driver_path):
                # Try parent directory
                driver_dir = os.path.dirname(driver_dir)
                chrome_driver_path = os.path.join(driver_dir, 'chromedriver.exe')
        
        print(f"ChromeDriver path: {chrome_driver_path}")
        
        if not os.path.exists(chrome_driver_path):
            raise Exception(f"ChromeDriver not found at {chrome_driver_path}")
        
        driver = webdriver.Chrome(
            service=Service(chrome_driver_path),
            options=chrome_options
        )
        print("[OK] Chrome driver initialized successfully")
        
        results = []
        
        print("\n" + "="*60)
        print(f"STEP 2: Searching for author: {author_name}")
        print("="*60)
        
        # Step 1: Search for author using regular Scholar search
        search_url = f"https://scholar.google.com/scholar?hl=en&as_sdt=0%2C5&q={author_name.replace(' ', '+')}&btnG="
        print(f"Search URL: {search_url}")
        
        driver.get(search_url)
        print("[OK] Page loaded successfully")
        time.sleep(3)
        
        # Step 2: Find and click first author profile link
        print("\n" + "="*60)
        print("STEP 3: Finding author profile...")
        print("="*60)
        
        try:
            # Cari link profile author (biasanya ada di nama penulis yang di-link)
            # Profile link biasanya di elemen <a> dengan href yang mengandung "/citations?user="
            print("Looking for profile links (a[href*='/citations?user='])...")
            author_links = driver.find_elements(By.CSS_SELECTOR, "a[href*='/citations?user=']")
            
            print(f"[OK] Found {len(author_links)} profile link(s)")
            
            if not author_links:
                print("[X] ERROR: No author profile found!")
                print(f"  Author searched: '{author_name}'")
                driver.quit()
                return []
            
            # Ambil profile link paling atas (pertama)
            author_profile_url = author_links[0].get_attribute('href')
            author_display_name = author_links[0].text
            
            print(f"[OK] Selected top profile: {author_display_name}")
            print(f"[OK] Profile URL: {author_profile_url}")
            
            print("\n" + "="*60)
            print("STEP 4: Opening author profile page...")
            print("="*60)
            
            # Buka halaman profile
            driver.get(author_profile_url)
            print("[OK] Profile page loaded successfully")
            time.sleep(3)
            
        except Exception as e:
            print(f"[X] ERROR finding author profile: {str(e)}")
            driver.quit()
            return []
        
        # Step 3: Get publications (ambil semua tanpa filter keyword dulu)
        print("\n" + "="*60)
        print(f"STEP 5: Extracting publications...")
        print("="*60)
        print(f"Target: Get top {max_results} publications")
        print(f"Keyword for matching: '{keyword}'")
        
        publications = driver.find_elements(By.CSS_SELECTOR, "tr.gsc_a_tr")
        print(f"[OK] Found {len(publications)} total publications on profile")
        
        if len(publications) == 0:
            print("[X] ERROR: No publications found on profile!")
            driver.quit()
            return []
        
        print(f"\nProcessing publications (max {max_results})...")
        print("-"*60)
        
        count = 0
        for pub in publications:
            if count >= max_results:
                break
            
            try:
                print(f"\n[Article {count + 1}]")
                
                # DARI HALAMAN PROFILE: Ambil data dasar artikel
                print("  >>> Getting basic info from profile page...")
                title_elem = pub.find_element(By.CSS_SELECTOR, "a.gsc_a_at")
                title = title_elem.text
                
                # PREPROCESSING DENGAN TRANSLASI
                print(f"  [PREPROCESS] Original: {title[:50]}...")
                
                # 1. Translate to Indonesian (untuk Sastrawi)
                try:
                    translated_title = translator.translate(title, src='en', dest='id').text
                    print(f"  [TRANSLATE] ID: {translated_title[:50]}...")
                except Exception as e:
                    print(f"  [TRANSLATE] Error: {str(e)}, using original")
                    translated_title = title
                
                # 2. Stemming (Sastrawi - Bahasa Indonesia)
                stemmed_title = stemmer.stem(translated_title)
                
                # 3. Stopword Removal
                clean_title = stopword.remove(stemmed_title)
                
                print(f"  [PREPROCESS] Cleaned: {clean_title[:50]}...")
                # ==========================================

                # TF-IDF + COSINE SIMILARITY
                # Translate keyword juga
                try:
                    translated_keyword = translator.translate(keyword, src='en', dest='id').text
                    print(f"  [TRANSLATE] Keyword ID: {translated_keyword}")
                except:
                    translated_keyword = keyword
                
                clean_keyword = stopword.remove(stemmer.stem(translated_keyword.lower()))

                similarity_score = compute_tfidf_similarity(
                    clean_keyword,
                    clean_title.lower()
                )
                
                article_link = title_elem.get_attribute('data-href')

                # Debug: cek juga href biasa kalau data-href kosong
                if not article_link:
                    article_link = title_elem.get_attribute('href')
                    print("  [WARN] data-href empty, using href instead")
                
                print(f"  Title: {title[:80]}{'...' if len(title) > 80 else ''}")
                print(f"  Article link (data-href): {article_link}")
                
                # Validasi format link
                if article_link and not article_link.startswith('http'):
                    print(f"  [INFO] Link is relative path, will prepend base URL")
                else:
                    print(f"  [INFO] Link is absolute URL")
                
                # Check if keyword matches (untuk kolom keyword_match)
                keyword_words = keyword.lower().split()
                title_lower = title.lower()
                keyword_match = any(word in title_lower for word in keyword_words)
                print(f"  Keyword Match: {'YES' if keyword_match else 'NO'}")
                
                # Get authors
                authors_elem = pub.find_element(By.CSS_SELECTOR, "div.gs_gray")
                authors = authors_elem.text
                print(f"  Authors: {authors[:50]}{'...' if len(authors) > 50 else ''}")
                
                # Get journal name
                try:
                    journal_elem = pub.find_elements(By.CSS_SELECTOR, "div.gs_gray")[1]
                    journal = journal_elem.text
                except:
                    journal = "N/A"
                print(f"  Journal: {journal[:40]}{'...' if len(journal) > 40 else ''}")
                
                # Get year
                try:
                    year_elem = pub.find_element(By.CSS_SELECTOR, "span.gsc_a_h")
                    year = year_elem.text
                except:
                    year = "N/A"
                print(f"  Year: {year}")
                
                # Get citations
                try:
                    citations_elem = pub.find_element(By.CSS_SELECTOR, "a.gsc_a_ac")
                    citations = citations_elem.text if citations_elem.text else "0"
                except:
                    citations = "0"
                print(f"  Citations: {citations}")
                
                # ====================================================================
                # MASUK KE HALAMAN DETAIL ARTIKEL untuk ambil link jurnal & tanggal
                # ====================================================================
                print("\n  >>> NOW OPENING ARTICLE DETAIL PAGE...")
                full_link = "N/A"
                publish_date = "N/A"
                
                try:
                    if article_link:
                        print("  Opening paper detail page...")
                        
                        # Build full URL
                        if article_link.startswith('http'):
                            detail_url = article_link
                        else:
                            detail_url = f"https://scholar.google.com{article_link}"
                        
                        print(f"  Detail URL: {detail_url}")
                        print(f"  Expected format: https://scholar.google.com/citations?view_op=view_citation&...")
                        
                        # Buka tab baru untuk detail page
                        driver.execute_script("window.open('');")
                        driver.switch_to.window(driver.window_handles[1])
                        driver.get(detail_url)
                        time.sleep(3)
                        
                        print("  " + "="*50)
                        print("  DETAIL PAGE - DEBUGGING")
                        print("  " + "="*50)
                        
                        # Debug: Save HTML untuk debugging jika diperlukan
                        if count == 0:  # Hanya untuk artikel pertama
                            try:
                                page_source = driver.page_source
                                with open('debug_page.html', 'w', encoding='utf-8') as f:
                                    f.write(page_source)
                                print("  [DEBUG] Page source saved to debug_page.html")
                            except:
                                pass
                        
                        # STEP 1: Cari LINK JURNAL
                        print("\n  [STEP 1] Looking for journal link...")
                        print("  Trying: #gsc_oci_title_gg area")
                        
                        try:
                            # Cari elemen gsc_oci_title_gg dulu
                            title_area = driver.find_element(By.ID, "gsc_oci_title_gg")
                            print("  [OK] Found gsc_oci_title_gg element")
                            
                            # Cari semua link di dalamnya
                            all_links = title_area.find_elements(By.TAG_NAME, "a")
                            print(f"  [DEBUG] Found {len(all_links)} link(s) in title area")
                            
                            for idx, link in enumerate(all_links):
                                link_href = link.get_attribute('href')
                                link_text = link.text
                                print(f"    Link {idx+1}: text='{link_text}' href='{link_href[:80] if link_href else 'None'}...'")
                            
                            if all_links:
                                full_link = all_links[0].get_attribute('href')
                                print(f"  [OK] Using first link: {full_link[:70]}...")
                            else:
                                print("  [X] No links found in gsc_oci_title_gg")
                                
                        except Exception as e:
                            print(f"  [X] Error finding title area: {str(e)}")
                        
                        # STEP 2: Cari TANGGAL TERBIT
                        print("\n  [STEP 2] Looking for publication date...")
                        print("  Trying: div.gs_scl rows")
                        
                        try:
                            # Cari semua baris field-value di halaman detail
                            rows = driver.find_elements(By.CSS_SELECTOR, "div.gs_scl")
                            print(f"  [DEBUG] Found {len(rows)} field rows (gs_scl)")
                            
                            if len(rows) == 0:
                                print("  [X] No gs_scl rows found! Trying alternative...")
                                # Coba cari dengan cara lain
                                rows = driver.find_elements(By.CSS_SELECTOR, "div.gsc_oci_field")
                                print(f"  [DEBUG] Found {len(rows)} gsc_oci_field elements")
                            
                            for row_idx, row in enumerate(rows):
                                try:
                                    # Setiap row punya field (label) dan value
                                    field = row.find_element(By.CSS_SELECTOR, "div.gsc_oci_field")
                                    value = row.find_element(By.CSS_SELECTOR, "div.gsc_oci_value")
                                    
                                    field_text = field.text.strip()
                                    value_text = value.text.strip()
                                    
                                    print(f"    Row {row_idx+1}: [{field_text}] = [{value_text}]")
                                    
                                    # Cek apakah ini field publication date
                                    if 'Publication date' in field_text or field_text == 'Date' or 'date' in field_text.lower():
                                        publish_date = value_text
                                        print(f"  [OK] >>> FOUND Publication date: {publish_date}")
                                        break
                                except Exception as e:
                                    print(f"    Row {row_idx+1}: Error reading - {str(e)}")
                                    continue
                            
                            if publish_date == "N/A":
                                print("  [X] Publication date field NOT FOUND")
                                print("  [INFO] Will use year from profile as fallback")
                                publish_date = year
                                
                        except Exception as e:
                            print(f"  [X] Error finding date: {str(e)}")
                            publish_date = year
                        
                        print("  " + "="*50)
                        print(f"  RESULT: Link={full_link[:50] if full_link != 'N/A' else 'N/A'}...")
                        print(f"  RESULT: Date={publish_date}")
                        print("  " + "="*50)
                        
                        # Tutup tab detail dan KEMBALI KE TAB PROFILE
                        print("\n  >>> Closing detail page and returning to profile...")
                        driver.close()
                        driver.switch_to.window(driver.window_handles[0])
                        print("  [OK] Back to profile page")
                        
                except Exception as e:
                    print(f"  [X] Error getting paper details: {str(e)}")
                    if len(driver.window_handles) > 1:
                        driver.close()
                        driver.switch_to.window(driver.window_handles[0])
                
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
                print(f"  [OK] Article {count} added to results")
                
            except Exception as e:
                print(f"  [X] ERROR processing publication: {str(e)}")
                continue
        
        driver.quit()
        
        print("\n" + "="*60)
        print("STEP 6: Crawling completed!")
        print("="*60)
        print(f"[OK] Total articles extracted: {len(results)}")
        print(f"[OK] Browser closed")
        results = sorted(results, key=lambda x: x['tfidf_similarity'], reverse=True)
        return results
        
        return results
        
    except Exception as e:
        print("\n" + "="*60)
        print("[X] FATAL ERROR!")
        print("="*60)
        print(f"Error: {str(e)}")
        print("Stack trace:")
        import traceback
        traceback.print_exc()
        
        if 'driver' in locals():
            driver.quit()
            print("[OK] Browser closed")
        return []

def compute_tfidf_similarity(keyword, document):
    """
    Compute TF-IDF cosine similarity between keyword and document
    Uses n-gram with n=1 (unigram) and n=2 (bigram)
    
    Args:
        keyword (str): search keyword
        document (str): document text (clean title)
    
    Returns:
        float: similarity score (0 - 1)
    """
    try:
        print(f"  [TF-IDF DEBUG] Keyword: '{keyword}'")
        print(f"  [TF-IDF DEBUG] Document: '{document}'")
        
        # Cek jika string kosong
        if not keyword.strip() or not document.strip():
            print("  [TF-IDF DEBUG] WARNING: Empty keyword or document!")
            return 0.0
        
        corpus = [keyword, document]
        
        # TfidfVectorizer dengan n-gram (unigram + bigram)
        vectorizer = TfidfVectorizer(
            ngram_range=(1, 2),  # n=1 (unigram) dan n=2 (bigram)
            lowercase=True,
            token_pattern=r'(?u)\b\w+\b'  # Token pattern untuk memproses kata
        )
        print(f"  [TF-IDF DEBUG] Using n-gram: unigram (n=1) + bigram (n=2)")
        
        tfidf_matrix = vectorizer.fit_transform(corpus)
        
        # Debug: tampilkan vocabulary dan matrix
        vocab = vectorizer.get_feature_names_out()
        print(f"  [TF-IDF DEBUG] Vocabulary ({len(vocab)} terms): {list(vocab)[:10]}{'...' if len(vocab) > 10 else ''}")
        print(f"  [TF-IDF DEBUG] TF-IDF Matrix shape: {tfidf_matrix.shape}")
        
        similarity = cosine_similarity(tfidf_matrix[0:1], tfidf_matrix[1:2])[0][0]
        print(f"  [TF-IDF DEBUG] Similarity score: {similarity}")
        
        return round(float(similarity), 4)
    except Exception as e:
        print(f"  [TF-IDF DEBUG] ERROR: {str(e)}")
        return 0.0
    
def main():
    """Main function to handle command line arguments"""
    
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
    
    # Run crawler
    results = crawl_google_scholar(author_name, keyword, max_results)
    
    # Prepare output data
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
    
    # Save to JSON file
    output_file = 'results.json'
    with open(output_file, 'w', encoding='utf-8') as f:
        json.dump(output, f, ensure_ascii=False, indent=2)
    
    print(f"\nResults saved to {output_file}")
    print("Success!")

if __name__ == "__main__":
    main()
