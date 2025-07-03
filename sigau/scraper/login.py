from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.chrome.service import Service
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.chrome.options import Options
from selenium.common.exceptions import TimeoutException
from config.settings import INTRANET_URL, SELENIUM_TIMEOUT
from webdriver_manager.chrome import ChromeDriverManager

CHROMEDRIVER_PATH = ChromeDriverManager().install()

def login(codigo, password):
    options = Options()
    options.add_argument("--headless=new")
    options.add_argument("--disable-gpu")
    options.add_argument("--no-sandbox")
    options.add_argument("--window-size=1920,1080")
    options.add_argument("--disable-dev-shm-usage")
    options.page_load_strategy = "eager"
    options.add_experimental_option("excludeSwitches", ["enable-logging"])

    service = Service(CHROMEDRIVER_PATH, log_path="NUL")
    driver = webdriver.Chrome(service=service, options=options)

    try:
        driver.set_page_load_timeout(SELENIUM_TIMEOUT)

        try:
            driver.get(INTRANET_URL)
        except TimeoutException:
            pass  # DOM mínimo cargado, continuamos

        wait = WebDriverWait(driver, SELENIUM_TIMEOUT)
        wait.until(EC.presence_of_element_located((By.ID, "UserName")))

        # Llenar campos de login
        driver.find_element(By.ID, "UserName").send_keys(codigo)
        driver.find_element(By.ID, "Password").send_keys(password)

        # Eliminar modal si aparece ANTES del clic
        try:
            announcement_input = driver.find_element(By.ID, "BeginningAnnouncement")
            if announcement_input.get_attribute("value") == "true":
                driver.execute_script("""
                    const modal = document.getElementById('BeginningAnnouncement_modal');
                    if (modal) {
                        modal.remove();
                    }
                """)
        except:
            pass  # Si no aparece el input o modal, seguimos

        # Hacer clic en el botón de login
        driver.find_element(By.ID, "m_login_signin_submit").click()

        # Esperamos que cargue el menú como confirmación de login exitoso
        wait.until(EC.presence_of_element_located((By.CLASS_NAME, "m-aside-menu")))

        return driver

    except Exception as e:
        print("Error en login:", str(e))
        driver.quit()
        return None