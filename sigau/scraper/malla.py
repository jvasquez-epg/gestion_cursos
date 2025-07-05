# ------------------------------------------------------------------------------
# Módulo de scraping para la malla curricular del estudiante
#
# Autor: ASI-GRUPO 5
# Año: 2025
#
# Este script accede a la página de malla curricular del sistema académico
# institucional mediante Selenium, analiza el DOM, y extrae todos los cursos
# registrados por ciclo. También identifica los prerrequisitos declarados.
#
# Características:
# - Detecta los bloques por ciclo (div.m-section)
# - Extrae: código, nombre, créditos, prerrequisitos
# - Interpreta encabezados de ciclos como "PRIMER", "SEGUNDO", "I", "II", etc.
# - Usa expresiones regulares para extraer los códigos y nombres de prerrequisitos
# - Devuelve una lista de diccionarios con todos los cursos
#
# Devuelve: lista de cursos o `None` si ocurre un error
# ------------------------------------------------------------------------------

import re
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from config.settings import INTRANET_ROOT, SELENIUM_TIMEOUT

def obtener_malla_curricular(driver):
    try:
        driver.get(f"{INTRANET_ROOT}/alumno/malla-curricular")

        wait = WebDriverWait(driver, SELENIUM_TIMEOUT)
        wait.until(EC.presence_of_all_elements_located((By.CSS_SELECTOR, "div.m-section")))

        cursos = []
        secciones = driver.find_elements(By.CSS_SELECTOR, "div.m-section")
        regex_prerrequisitos = re.compile(r'(\w{9})\s*-\s*([A-ZÁÉÍÓÚÑ\s]+)', re.UNICODE)

        for sec in secciones:
            h5 = sec.find_elements(By.TAG_NAME, "h5")
            ciclo_text = h5[0].text.strip() if h5 else ""
            if not ciclo_text:
                continue

            m = re.search(r'(\d+)', ciclo_text)
            if m:
                ciclo_num = int(m.group(1))
            else:
                txt = ciclo_text.upper()
                if 'PRIMER' in txt or txt.startswith('I '):
                    ciclo_num = 1
                elif 'SEGUND' in txt or txt.startswith('II '):
                    ciclo_num = 2
                else:
                    continue

            for tr in sec.find_elements(By.CSS_SELECTOR, "table tbody tr"):
                cols = tr.find_elements(By.TAG_NAME, "td")
                if len(cols) < 3:
                    continue

                codigo = cols[0].text.strip()
                nombre = cols[1].text.strip()
                try:
                    creditos = float(cols[2].text.strip())
                except ValueError:
                    creditos = 0.0

                prerrequisitos = []
                if len(cols) > 7:
                    raw = cols[7].text.strip()
                    for cod, nom in regex_prerrequisitos.findall(raw):
                        prerrequisitos.append({
                            'codigo': cod.strip(),
                            'nombre': nom.strip()
                        })

                cursos.append({
                    "ciclo": ciclo_num,
                    "codigo": codigo,
                    "nombre": nombre,
                    "creditos": creditos,
                    "prerrequisitos": prerrequisitos
                })

        return cursos

    except Exception:
        return None