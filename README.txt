SEEKINO
=======

Seekino ir kino seansu un bilešu rezervacijas timekla lietotne. Projekts sastav no Vue 3 frontend dalas un Laravel backend API. Lietotaji var apskatit filmas, seansus, rezervet sedvietas, parvaldit savu profilu un atstat atsauksmes. Administratoram ir pieejamas sadalas filmu, seansu un lietotaju parvaldibai.


GALVENAS IESPEJAS
-----------------

- Filmu saraksts un detalizeta filmas informacija.
- Seansu saraksts ar pieejamajiem kino seansiem.
- Sedvietu izvele un rezervacijas izveide.
- Lietotaja registracija, pieslegsanas un profila parvaldiba.
- Lietotaja rezervaciju apskate un rezervacijas atcelsana.
- Filmu atsauksmju pievienosana un skatisana.
- Administratora panelis filmu parvaldibai.
- Administratora panelis seansu parvaldibai.
- Administratora panelis lietotaju parvaldibai.


TEHNOLOGIJAS
------------

Frontend:
- Vue 3
- Vite
- Vue Router
- Vuetify

Backend:
- PHP 8.2+
- Laravel 12
- Laravel migrations un seeders
- API marsruti faila backend/laravel-app/routes/api.php


PROJEKTA STRUKTURA
------------------

frontend/vue-project
    Vue lietotne, skati, marsrutetajs, komponentes un autentifikacijas serviss.

backend/laravel-app
    Laravel API, kontrolieri, modeli, migracijas, seeders un datubazes konfiguracija.

README.txt
    Projekta kopejais apraksts un palaisanas instrukcija.


PRASIBAS
--------

- Node.js 20.19.0 vai jaunaks
- npm
- PHP 8.2 vai jaunaks
- Composer
- MySQL vai cita Laravel konfiguracija noradita datubaze


UZSTADISANA
-----------

1. Backend sagatavosana:

   cd backend/laravel-app
   composer install
   cp .env.example .env
   php artisan key:generate

2. Datubazes konfiguresana:

   Atver failu backend/laravel-app/.env un noradi savas datubazes piesleguma datus, piemeram:

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=seekino
   DB_USERNAME=root
   DB_PASSWORD=

3. Migraciju un sakuma datu palaisana:

   php artisan migrate
   php artisan db:seed

   Seeders izveido lietotajus, filmas, zanrus, zales, sedvietas, seansus un atsauksmes.

4. Frontend sagatavosana:

   cd ../../frontend/vue-project
   npm install

5. Ja nepieciesams, frontend vide noradi API adresi:

   Izveido failu frontend/vue-project/.env un pievieno:

   VITE_API_BASE_URL=http://127.0.0.1:8000


PALAISANA
---------

Backend serveris:

   cd backend/laravel-app
   php artisan serve

Pec noklusejuma Laravel API bus pieejams:

   http://127.0.0.1:8000

Frontend serveris:

   cd frontend/vue-project
   npm run dev

Pec noklusejuma Vue lietotne bus pieejama Vite noraditaja adrese, parasti:

   http://localhost:5173


TESTA LIETOTAJI
---------------

Pec seeders palaisanas ir pieejami sadi lietotaji:

Parasts lietotajs:
   E-pasts: janis@example.com
   Parole: password

Administrators:
   E-pasts: anna@example.com
   Parole: password


SVARIGAKIE API MARSRUTI
-----------------------

Autentifikacija:
- POST /api/register
- POST /api/login
- GET /api/me
- POST /api/logout
- PUT /api/profile

Filmas un seansi:
- GET /api/movies
- GET /api/movies/{movie}
- GET /api/movies/{movie}/feedbacks
- POST /api/movies/{movie}/feedbacks
- GET /api/screenings
- GET /api/screenings/{screening}

Rezervacijas:
- POST /api/reservations
- GET /api/profile/reservations
- PATCH /api/profile/reservations/{reservation}/cancel

Administresana:
- GET /api/admin/movies
- POST /api/admin/movies
- PUT /api/admin/movies/{movie}
- DELETE /api/admin/movies/{movie}
- GET /api/admin/screenings
- POST /api/admin/screenings
- PUT /api/admin/screenings/{screening}
- DELETE /api/admin/screenings/{screening}
- GET /api/admin/users
- PUT /api/admin/users/{user}
- DELETE /api/admin/users/{user}


BUILD KOMANDAS
--------------

Frontend produkcijas build:

   cd frontend/vue-project
   npm run build

Frontend build priekskatijums:

   npm run preview

Backend testi:

   cd backend/laravel-app
   php artisan test


PIEZIMES
--------

- Frontend pec noklusejuma piesledzas API adresei http://127.0.0.1:8000.
- Administratora sadalas ir paredzetas lietotajiem ar admin lomu.
- Ja datubaze ir tuksa, vispirms japalaiz migracijas un seeders.
- Projekta rezerves datubazes fails atrodas backend/laravel-app/seekino_backup.sql.
