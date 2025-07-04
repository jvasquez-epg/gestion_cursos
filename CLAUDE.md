# CodeViz Research Context

> **Note**: This file contains research context from CodeViz. Most recent contexts are at the bottom.

---

## Research Query

the dependence very detailed of each file

*Session: f683f32df46d0bc7fb7ad8817766ca95 | Generated: 7/4/2025, 12:36:05 AM*

### Analysis Summary

# Codebase Dependency Report

This report details the dependencies of key files within the `gestion_cursos` codebase, providing insights into their purpose, internal structure, and external relationships.

## High-Level Architecture

The application appears to be a PHP-based web application with a Python component for scraping data. The core components include:

*   **Admin Module**: Manages administrative tasks such as user management, course assignments, and period configurations.
*   **Student Module**: Provides functionalities for students, including course viewing, profile management, and progress tracking.
*   **Configuration**: Contains core application settings and database connection.
*   **Components**: Reusable UI elements like headers and sidebars.
*   **SIGAU Scraper**: A Python-based module responsible for scraping data, likely related to academic information.

## Core Application Files

### [index.php](index.php)
The main entry point of the application. It likely handles routing and initial setup.

### [login.php](login.php)
Handles user authentication and login processes.

### [logout.php](logout.php)
Manages user logout.

### [signup.php](signup.php)
Handles user registration.

## Configuration

### [config/conexion.php](config/conexion.php)
*   **Purpose**: Establishes the database connection for the PHP application.
*   **Internal Parts**: Contains database credentials and the PDO connection object.
*   **External Relationships**: Used by various models and controllers to interact with the database.

### [config/config.php](config/config.php)
*   **Purpose**: Stores general configuration settings for the PHP application.
*   **Internal Parts**: Defines constants or variables for application-wide settings.
*   **External Relationships**: Included by various PHP files to access configuration values.

## Admin Module ([admin/](admin/))

The `admin` module is responsible for administrative functionalities. It follows a Model-View-Controller (MVC) pattern.

### Controllers ([admin/controllers/](admin/controllers/))
These files handle the logic for administrative actions, interacting with models and preparing data for views.

*   **[AsignacionController.php](admin/controllers/AsignacionController.php)**: Manages course assignment logic.
*   **[DocenteController.php](admin/controllers/DocenteController.php)**: Handles teacher-related operations.
*   **[PeriodoController.php](admin/controllers/PeriodoController.php)**: Manages academic period configurations.
*   **[PublicarController.php](admin/controllers/PublicarController.php)**: Likely handles publishing or making data available.
*   **[SolicitudController.php](admin/controllers/SolicitudController.php)**: Manages course request/solicitation processes.
*   **[UsuarioController.php](admin/controllers/UsuarioController.php)**: Handles user management within the admin panel.

### Models ([admin/models/](admin/models/))
These files interact with the database to perform CRUD operations for administrative data.

*   **[AsignacionModel.php](admin/models/AsignacionModel.php)**: Database operations for course assignments.
*   **[DocenteModel.php](admin/models/DocenteModel.php)**: Database operations for teacher information.
*   **[PeriodoModel.php](admin/models/PeriodoModel.php)**: Database operations for academic periods.
*   **[SolicitudModel.php](admin/models/SolicitudModel.php)**: Database operations for course requests.
*   **[UsuarioModel.php](admin/models/UsuarioModel.php)**: Database operations for user management.

### Views ([admin/views/](admin/views/))
These files are responsible for rendering the user interface for the admin panel.

*   **[asignaciones_detalle.php](admin/views/asignaciones_detalle.php)**: Displays details of a specific course assignment.
*   **[asignaciones.php](admin/views/asignaciones.php)**: Lists all course assignments.
*   **[docentes_form.php](admin/views/docentes_form.php)**: Form for adding or editing teacher information.
*   **[docentes_list.php](admin/views/docentes_list.php)**: Lists all teachers.
*   **[periodos_create.php](admin/views/periodos_create.php)**: Form for creating new academic periods.
*   **[periodos.php](admin/views/periodos.php)**: Lists all academic periods.
*   **[solicitudes_detalle.php](admin/views/solicitudes_detalle.php)**: Displays details of a specific course request.
*   **[solicitudes.php](admin/views/solicitudes.php)**: Lists all course requests.
*   **[usuarios_dashboard.php](admin/views/usuarios_dashboard.php)**: Admin dashboard for user overview.
*   **[usuarios_form.php](admin/views/usuarios_form.php)**: Form for adding or editing user information.
*   **[usuarios_list.php](admin/views/usuarios_list.php)**: Lists all users.

## Student Module ([estudiante/](estudiante/))

The `estudiante` module provides functionalities for students, also following an MVC pattern.

### Controllers ([estudiante/controllers/](estudiante/controllers/))
These files handle the logic for student-specific actions.

*   **[CursoController.php](estudiante/controllers/CursoController.php)**: Manages student course-related logic.
*   **[DashboardController.php](estudiante/controllers/DashboardController.php)**: Handles student dashboard logic.
*   **[MallaController.php](estudiante/controllers/MallaController.php)**: Manages academic curriculum (malla) logic.
*   **[PerfilController.php](estudiante/controllers/PerfilController.php)**: Handles student profile management.
*   **[ProgresoController.php](estudiante/controllers/ProgresoController.php)**: Manages student academic progress.
*   **[SolicitudesController.php](estudiante/controllers/SolicitudesController.php)**: Handles student course requests.

### Models ([estudiante/models/](estudiante/models/))
These files interact with the database for student-related data.

*   **[CursoModel.php](estudiante/models/CursoModel.php)**: Database operations for student courses.
*   **[DashboardModel.php](estudiante/models/DashboardModel.php)**: Database operations for student dashboard data.
*   **[MallaModel.php](estudiante/models/MallaModel.php)**: Database operations for academic curriculum.
*   **[PeriodoModel.php](estudiante/models/PeriodoModel.php)**: Database operations for academic periods (likely shared with admin).
*   **[ProgresoModel.php](estudiante/models/ProgresoModel.php)**: Database operations for student progress.
*   **[SolicitudModel.php](estudiante/models/SolicitudModel.php)**: Database operations for student course requests.
*   **[UsuarioModel.php](estudiante/models/UsuarioModel.php)**: Database operations for user information (likely shared with admin).

### Services ([estudiante/services/](estudiante/services/))

*   **[DocumentGenerator.php](estudiante/services/DocumentGenerator.php)**:
    *   **Purpose**: Generates documents, likely for course requests or academic records.
    *   **Internal Parts**: Contains logic for document creation, potentially using libraries like PHPWord or Dompdf.
    *   **External Relationships**: Interacts with the [assets/templates/Solicitud_Apertura_Curso.docx](assets/templates/Solicitud_Apertura_Curso.docx) template and potentially other models to fetch data for document generation.

### Views ([estudiante/views/](estudiante/views/))
These files render the user interface for the student module.

*   **[cambiar_password.php](estudiante/views/cambiar_password.php)**: Form for changing student password.
*   **[cursos_dashboard.php](estudiante/views/cursos_dashboard.php)**: Student dashboard for courses.
*   **[cursos_inaccesible.php](estudiante/views/cursos_inaccesible.php)**: Page for inaccessible courses.
*   **[dashboard.php](estudiante/views/dashboard.php)**: Main student dashboard.
*   **[malla_dashboard.php](estudiante/views/malla_dashboard.php)**: Student dashboard for academic curriculum.
*   **[perfil.php](estudiante/views/perfil.php)**: Student profile page.
*   **[progreso_dashboard.php](estudiante/views/progreso_dashboard.php)**: Student dashboard for academic progress.
*   **[solicitudes_dashboard.php](estudiante/views/solicitudes_dashboard.php)**: Student dashboard for course requests.

## Components ([components/](components/))

Reusable UI components.

*   **[header_main.php](components/header_main.php)**: Main application header.
*   **[header_user.php](components/header_user.php)**: User-specific header.
*   **[sidebar.php](components/sidebar.php)**: Application sidebar/navigation.

## SIGAU Scraper Module ([sigau/](sigau/))

This module is a Python application for scraping data.

### [main.py](sigau/main.py)
*   **Purpose**: The main entry point for the SIGAU scraper application.
*   **Internal Parts**: Orchestrates the scraping process, potentially calling functions from other scraper files.
*   **External Relationships**: Depends on modules within [sigau/scraper/](sigau/scraper/) for specific scraping tasks and uses configurations from [sigau/config/settings.py](sigau/config/settings.py).

### [requirements.txt](sigau/requirements.txt)
*   **Purpose**: Lists the Python dependencies required for the SIGAU scraper.
*   **Internal Parts**: Contains a list of Python package names and their versions.
*   **External Relationships**: Used by `pip` to install necessary libraries for the Python environment.

### Config ([sigau/config/](sigau/config/))

*   **[settings.py](sigau/config/settings.py)**:
    *   **Purpose**: Stores configuration settings for the SIGAU scraper, such as URLs, credentials, or scraping parameters.
    *   **Internal Parts**: Defines variables for various settings.
    *   **External Relationships**: Imported and used by other Python scripts in the `sigau` module.

### Scraper ([sigau/scraper/](sigau/scraper/))

These Python files contain the specific logic for scraping different types of data.

*   **[login.py](sigau/scraper/login.py)**: Handles the login process for the SIGAU system.
*   **[malla.py](sigau/scraper/malla.py)**: Scrapes academic curriculum (malla) data.
*   **[perfil.py](sigau/scraper/perfil.py)**: Scrapes user profile information.
*   **[progreso.py](sigau/scraper/progreso.py)**: Scrapes academic progress data.

## Vendor Libraries ([vendor/](vendor/))

This directory contains third-party libraries managed by Composer.

### [autoload.php](vendor/autoload.php)
*   **Purpose**: Composer's autoloader, responsible for loading classes from installed packages.
*   **Internal Parts**: Contains logic to map namespaces and class names to file paths.
*   **External Relationships**: Included at the beginning of most PHP scripts to enable autoloading of dependencies.

### chillerlan/php-qrcode ([vendor/chillerlan/php-qrcode/](vendor/chillerlan/php-qrcode/))
*   **Purpose**: A PHP library for generating QR codes.
*   **Internal Parts**: Contains classes and functions for QR code generation.
*   **External Relationships**: Used by any PHP file that needs to generate QR codes.

### chillerlan/php-settings-container ([vendor/chillerlan/php-settings-container/](vendor/chillerlan/php-settings-container/))
*   **Purpose**: A PHP library for managing settings.
*   **Internal Parts**: Provides a container for application settings.
*   **External Relationships**: Used by any PHP file that needs a structured way to handle settings.

### dompdf/dompdf ([vendor/dompdf/dompdf/](vendor/dompdf/dompdf/))
*   **Purpose**: A PHP library for converting HTML to PDF.
*   **Internal Parts**: Contains classes and functions for PDF rendering.
*   **External Relationships**: Used by any PHP file that needs to generate PDF documents from HTML, likely by [estudiante/services/DocumentGenerator.php](estudiante/services/DocumentGenerator.php).

### phpoffice/phpword ([vendor/phpoffice/phpword/](vendor/phpoffice/phpword/))
*   **Purpose**: A PHP library for reading and writing Word documents.
*   **Internal Parts**: Contains classes and functions for Word document manipulation.
*   **External Relationships**: Used by any PHP file that needs to generate or process Word documents, likely by [estudiante/services/DocumentGenerator.php](estudiante/services/DocumentGenerator.php).

