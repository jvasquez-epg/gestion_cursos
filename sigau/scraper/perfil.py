from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from config.settings import INTRANET_ROOT, SELENIUM_TIMEOUT


def obtener_datos_perfil(driver):
    try:
        driver.get(f"{INTRANET_ROOT}/perfil")
        wait = WebDriverWait(driver, 10)
        wait.until(EC.presence_of_element_located((By.CLASS_NAME, "m-form")))

        datos = {}

        full_name = driver.find_element(
            By.XPATH,
            '//label[contains(text(),"Nombre Completo")]/following-sibling::div/input'
        ).get_attribute("value").strip()

        partes = full_name.split()
        datos['apellido_paterno'] = partes[0]
        datos['apellido_materno'] = partes[1] if len(partes) > 1 else ''
        datos['nombres'] = ' '.join(partes[2:]) if len(partes) > 2 else ''

        dni_span = driver.find_element(By.CLASS_NAME, "m-card-profile__email")
        datos['dni'] = dni_span.text.strip()

        datos['escuela'] = driver.find_element(
            By.XPATH,
            '//label[contains(text(),"ESCUELA PROFESIONAL")]/following-sibling::div/input'
        ).get_attribute("value")

        return datos
    except Exception:
        return None