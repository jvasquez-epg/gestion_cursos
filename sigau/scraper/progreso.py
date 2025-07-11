# ------------------------------------------------------------------------------
# Módulo de scraping para obtener la situación académica del estudiante
#
# Autor: ASI-GRUPO 5
# Año: 2025
#
# Este script automatiza el acceso a la sección de "Progreso Académico" del
# sistema institucional, y extrae la lista de cursos junto con su estado
# (Cumplido o Pendiente), tanto del plan regular como de los cursos electivos.
#
# Características:
# - Accede a múltiples tablas dentro de acordeones y secciones específicas
# - Extrae código, nombre, créditos y estado de cada curso
# - Considera tabla adicional de cursos electivos (si existe)
# - Normaliza los estados no válidos como "Pendiente"
#
# Devuelve: diccionario con la clave 'cursos', que contiene una lista de dicts
# ------------------------------------------------------------------------------

from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from config.settings import INTRANET_ROOT, SELENIUM_TIMEOUT

def obtener_situacion_academica(driver):
    try:
        driver.get(f"{INTRANET_ROOT}/alumno/progreso")
        wait = WebDriverWait(driver, SELENIUM_TIMEOUT)
        wait.until(EC.presence_of_element_located((By.CSS_SELECTOR, "div.m-accordion__item-content table tbody tr")))

        cursos = []
        tablas = driver.find_elements(By.CSS_SELECTOR, 'div.m-accordion__item-content table')
        for tabla in tablas:
            for tr in tabla.find_elements(By.CSS_SELECTOR, 'tbody tr'):
                cols = tr.find_elements(By.TAG_NAME, 'td')
                if len(cols) < 6:
                    continue
                texto = cols[0].text.strip()
                if ' - ' in texto:
                    codigo, nombre = texto.split(' - ', 1)
                else:
                    codigo, nombre = None, texto
                try: cred = float(cols[1].text.strip())
                except: cred = 0.0
                estado = cols[5].text.strip()
                if estado not in ["Pendiente", "Cumplido"]:
                    estado = "Pendiente"
                cursos.append({
                    'codigo': codigo,
                    'nombre': nombre,
                    'creditos': cred,
                    'estado': estado
                })
        try:
            tabla_electivos = driver.find_element(By.CSS_SELECTOR, "div.elective-courses-datatable table")
            filas = tabla_electivos.find_elements(By.CSS_SELECTOR, "tbody tr")
            for tr in filas:
                cols = tr.find_elements(By.TAG_NAME, 'td')
                if len(cols) < 7:
                    continue
                texto = cols[0].text.strip()
                if ' - ' in texto:
                    codigo, nombre = texto.split(' - ', 1)
                else:
                    codigo, nombre = None, texto
                try: cred = float(cols[2].text.strip())
                except: cred = 0.0
                estado = cols[6].text.strip()
                if estado not in ["Pendiente", "Cumplido"]:
                    estado = "Pendiente"
                cursos.append({
                    'codigo': codigo,
                    'nombre': nombre,
                    'creditos': cred,
                    'estado': estado
                })
        except:
            pass

        return {
            'cursos': cursos
        }
    except Exception:
        return {
            'cursos': []
        }


