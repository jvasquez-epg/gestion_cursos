# ------------------------------------------------------------------------------
# Script principal de scraping académico para estudiantes
#
# Autor: José Vásquez Zevallos
# Año: 2025
#
# Este script automatiza el ingreso al sistema académico de la universidad
# para extraer la información personal del estudiante, la malla curricular
# correspondiente y su progreso académico. 
#
# Características:
# - Inicia sesión en el sistema con código y contraseña.
# - Obtiene el perfil del estudiante y valida que pertenezca a la escuela
#   de Ingeniería de Sistemas e Informática.
# - Extrae la malla curricular completa del estudiante.
# - Extrae los cursos cursados y su estado (cumplido o pendiente).
# - Detecta y agrega cursos electivos pendientes según reglas definidas.
# - Elimina electivos pendientes de ciclos ya cumplidos.
# - Devuelve un JSON con los datos consolidados.
#
# Requiere módulos internos para:
# - login (autenticación con Selenium)
# - perfil (datos personales)
# - malla (estructura curricular)
# - progreso (situación académica)
# ------------------------------------------------------------------------------ 

import sys
import json
import io
sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8')

from scraper.login import login
from scraper.malla import obtener_malla_curricular
from scraper.perfil import obtener_datos_perfil
from scraper.progreso import obtener_situacion_academica

ESCUELA_PERMITIDA = "INGENIERIA DE SISTEMAS E INFORMÁTICA"

def error_json(mensaje):
    print(json.dumps({"error": mensaje}, ensure_ascii=False))
    sys.exit(0)

def es_electivo(curso):
    nombre = curso.get("nombre", "").upper()
    confirmados = {
        "TECNOLOGIA MULTIMEDIA",
        "MARKETING DIGITAL",
        "GESTION DE RECURSOS HUMANOS",
        "EMPRENDIMIENTO DIGITAL",
        "SISTEMAS CONTABLES",
        "GESTION FINANCIERA",
        "COMPUTACION PARALELA",
        "COMPUTACION GRAFICA",
        "BIOINFORMATICA",
        "COMPUTACION MOVIL Y UBICUA",
        "PEDAGOGIA INFORMATICA",
        "PERITAJE INFORMATICO",
        "ECONOMIA DIGITAL",
        "CALIDAD DE SOFTWARE"
    }
    return nombre in confirmados

def agregar_electivos_pendientes(malla, progreso):
    from collections import defaultdict

    # Identifica electivos de la malla y agrúpalos por ciclo
    electivos = [c for c in malla if es_electivo(c)]
    ciclos = defaultdict(list)
    for c in electivos:
        ciclos[c['ciclo']].append(c)

    codigos_presentes = {c['codigo'] for c in progreso}
    codigos_cursados = {c['codigo'] for c in progreso if c.get('estado') == 'Cumplido'}

    # Añade faltantes sólo en ciclos sin electivos cumplidos
    for ciclo, cursos in ciclos.items():
        if any(c['codigo'] in codigos_cursados for c in cursos):
            continue
        for c in cursos:
            if c['codigo'] not in codigos_presentes:
                progreso.append({
                    'codigo': c['codigo'],
                    'nombre': c['nombre'],
                    'creditos': c['creditos'],
                    'estado': 'Pendiente',
                    'fuente': 'malla'
                })


def filtrar_electivos_por_ciclo(progreso, malla):
    # Mapea código→ciclo
    code_to_ciclo = {c['codigo']: c['ciclo'] for c in malla}
    # Anota ciclo en cada curso de progreso
    for p in progreso:
        p['ciclo'] = code_to_ciclo.get(p['codigo'])

    # Ciclos donde ya hay un electivo cumplido
    ciclos_cumplidos = {
        p['ciclo'] for p in progreso
        if es_electivo(p) and p.get('estado') == 'Cumplido'
    }

    # Filtra fuera los pendientes de esos ciclos
    return [
        p for p in progreso
        if not (
            es_electivo(p) and p.get('estado') == 'Pendiente' and p.get('ciclo') in ciclos_cumplidos
        )
    ]


def main():
    if len(sys.argv) < 3:
        error_json("Uso: python main.py <codigo> <password>")

    codigo, password = sys.argv[1], sys.argv[2]

    driver = login(codigo, password)
    if not driver:
        error_json("No se pudo iniciar sesión. Revisa tus credenciales.")

    try:
        perfil = obtener_datos_perfil(driver) or {}
        escuela = perfil.get("escuela", "").strip().upper()
        if escuela != ESCUELA_PERMITIDA:
            error_json(f"Solo permitido para {ESCUELA_PERMITIDA}.")

        malla = obtener_malla_curricular(driver) or []
        progreso = obtener_situacion_academica(driver).get("cursos", [])

        agregar_electivos_pendientes(malla, progreso)
        progreso = filtrar_electivos_por_ciclo(progreso, malla)

        resultado = {
            "perfil": perfil,
            "malla": malla,
            "progreso": progreso
        }
        print(json.dumps(resultado, indent=2, ensure_ascii=False))

    except Exception as e:
        error_json(f"Error inesperado durante el scraping: {e}")

    finally:
        driver.quit()

if __name__ == "__main__":
    main()