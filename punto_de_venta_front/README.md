# punto_de_venta_linux

🧭 Flujo de trabajo con ramas Git: main, dev y vat
Este proyecto utiliza un flujo de trabajo basado en ramas para mantener el código organizado y facilitar la colaboración. A continuación se describen las instrucciones para trabajar correctamente con las ramas main, dev y vat.
🌿 Ramas principales
|  |  | 
| main :Rama estable. Contiene el código listo para producción.
| dev  :Rama de desarrollo. Aquí se integran nuevas funcionalidades antes de pasar a main.
| vat :Rama experimental. Se usa para pruebas, prototipos o ideas en desarrollo.



🛠️ Cómo trabajar con las ramas
1. Clona el repositorio
git clone
cd repositorio


2. Cambia a la rama de desarrollo
git checkout dev


3. Crea una nueva rama para tu tarea
git checkout -b (siempre iniciar con la inicial de cada rama princila)nombre-de-tu-rama
DEV_inicio_sesion
VAT_prueba_tiket

4. Realiza tus cambios y haz commits descriptivos
git add .
git commit -m "Agrega formulario de login con validación"


💡 Usa mensajes de commit claros y concisos. Evita mensajes genéricos como "update" o "fix".

5. Sube tu rama al repositorio remoto
git push origin nombre-de-tu-rama



🔁 Cómo hacer un Pull Request (PR)
- Ve a GitHub y abre un Pull Request desde tu rama hacia dev.
- Agrega una descripción clara de los cambios realizados.
- Solicita revisión si es necesario.
- Una vez aprobado, se hace merge a dev.
✅ Los cambios en main solo se hacen desde dev mediante un Pull Request aprobado.


🧪 Rama vat: uso experimental

📌 Buenas prácticas

## Estructura reorganizada (backend / frontend)

He reestructurado el proyecto en dos entradas principales: `backend/` y `frontend/`.

- `backend/public/index.php` - Entrada para acciones de backend (controladores, API, administración).
- `frontend/public/index.php` - Entrada pública del sitio que usa los controladores y vistas en `app/`.
- `public/index.php` - Compatibility: reenvía a `frontend/public/index.php` si existe.

Cómo ejecutar en tu entorno local (XAMPP):

1. Apunta tu VirtualHost o DocumentRoot a `c:/xampp/htdocs/punto_de_venta_front/public`.
2. Accede a `http://localhost/` para ver el frontend (se reenvía internamente).

Notas:
- Los archivos de aplicación (controllers, helpers y views) permanecen en `app/` y son usados por ambas entradas.
- Las dependencias de Composer siguen en `vendor/` y son cargadas por los entrypoints.

Probar localmente (XAMPP)

1. Asegúrate de que `c:/xampp/htdocs/punto_de_venta_front/public` sea el DocumentRoot de tu VirtualHost o sitio en XAMPP.
2. Reinicia Apache.
3. Abre en el navegador: http://localhost/ — esto cargará `public/index.php` que reenvía al `frontend`.

Comprobaciones que ya hice
- Creé `backend/public/index.php` y `frontend/public/index.php`.
- Moví controladores y helpers a `backend/app` y vistas a `frontend/app/Views`.
- Actualicé `public/index.php` para mantener compatibilidad.

Notas finales
- Si quieres que mueva también `vendor/` dentro de `backend/`, dime y lo hago (recomiendo mantener `vendor/` en la raíz para simplicidad).
- Si aparecen errores en tiempo de ejecución relacionados con rutas, comparte el log de Apache/PHP y lo depuro.

