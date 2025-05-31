Solo se requieren 2 archivos para correr la aplicación
    Dockerfile
    docker-compose.yml

El repo es publico y se puede verificar aqui:
https://github.com/Crism00/WATest

Se deben descargar y poner en alguna carpeta donde se desee trabajar
Se abre una terminal o cmd en la carpeta donde se guardaron estos 2 archivos.
Una vez dentro se deben correr los siguientes comandos.
Comandos:
    1:
    (Desde la carpeta)
    docker-compose up --build    
    2:
    (Este se tiene que correr desde una consola que tenga abierta la carpeta donde se encuentren los archivos)
    docker cp ./.env laravel_app:/var/www/html/.env
    3:
    docker exec laravel_app php artisan key:generate
    4:
    (Una vez finalicen de construirse ambos contenedores)
    docker exec laravel_app php artisan migrate
    5:
    docker exec laravel_app php artisan serve --host=0.0.0.0 --port=8000
    6:
    (Se requiere tener instalado ngrok en la maquina y se corre desde una terminal y se deja corriendo durante el testing de la app)
    ngrok config add-authtoken 2xn7WaQBiC73gVHe7Dk2fGnRHS8_3sk1cnTNfV9ngKYzgvChy
    6:
    ngrok http --url=huge-badger-happily.ngrok-free.app 8000

A partir de este momento se puede testear la app accediendo a este link:
https://huge-badger-happily.ngrok-free.app/

Se pueden sincronizar los Contactos existentes en Zoho con este comando
docker exec laravel_app php artisan app:sync-zoho-contacts


Toda la interfaz es muy intuitiva y simple.
Al entrar a la app aparecera una pantalla de inició de laravel predeterminada, a la derecha se encuentra un boton que dice "Contactos"
Una vez entramos a contactos estara una tabla que mostrara los datos de los contactos registrados, se podran añadir y registrar nuevos datos
Todo es bidireccional, existen varias validaciones para los numeros, y otras cosas, el formulario de los cursos lo deje en un input text bàsico, asumo esto no sera un problema, se que lo correcto seria tener una tabla de los cursos disponibles y tener relación en las tablas, esto es meramente para el testeo de la aplicación.

Cuando se registra un nuevo usuario en el crm, se reflejara al recargar nuestra página, se podria hacer a tiempo real, por el hecho de ser una prueba me tome la libertad de dejarlo asi.
De igual forma al registrar un contacto en nuestra app se registrara en el CRM

Si se intenta registrar un telefono que ya existe en el crm, pero en nuestra app no, automaticamente se traeran los datos del CRM y se registrara en nuestra app.
En el formulario del crm existe la validación telefono unique

