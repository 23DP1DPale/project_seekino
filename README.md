# Seekino

**Seekino** ir kino seansu un biļešu rezervācijas tīmekļa lietotne ar Vue 3 frontend daļu un Laravel API backend daļu. Lietotāji var apskatīt filmas, izvēlēties seansus, rezervēt sēdvietas, pārvaldīt profilu un atstāt atsauksmes. Administratoriem ir pieejama filmu, seansu un lietotāju pārvaldība.

![Vue](https://img.shields.io/badge/Vue-3-42b883?style=for-the-badge&logo=vue.js&logoColor=white)
![Vite](https://img.shields.io/badge/Vite-7-646cff?style=for-the-badge&logo=vite&logoColor=white)
![Laravel](https://img.shields.io/badge/Laravel-12-ff2d20?style=for-the-badge&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.2+-777bb4?style=for-the-badge&logo=php&logoColor=white)

---

## Saturs

- [Funkcijas](#funkcijas)
- [Tehnoloģijas](#tehnoloģijas)
- [Projekta struktūra](#projekta-struktūra)
- [Prasības](#prasības)
- [Uzstādīšana](#uzstādīšana)
- [Palaišana](#palaišana)
- [Testa lietotāji](#testa-lietotāji)
- [API maršruti](#api-maršruti)
- [Build un testi](#build-un-testi)

---

## Funkcijas

| Lietotāji | Administratori |
| --- | --- |
| Filmu saraksta apskate | Filmu pievienošana, labošana un dzēšana |
| Filmas detalizēta informācija | Seansu pievienošana, labošana un dzēšana |
| Seansu apskate | Lietotāju pārvaldība |
| Sēdvietu izvēle un rezervācija | Žanru, zāļu un seansu datu izmantošana |
| Profila pārvaldība | Administrācijas skati frontend pusē |
| Rezervāciju apskate un atcelšana |  |
| Atsauksmju pievienošana filmām |  |

---

## Tehnoloģijas

### Frontend

- Vue 3
- Vite
- Vue Router
- Vuetify
- Fetch API

### Backend

- PHP 8.2+
- Laravel 12
- Laravel migrations
- Laravel seeders
- API kontrolieri un Eloquent modeļi

---

## Projekta struktūra

```text
project_seekino/
├── backend/
│   └── laravel-app/
│       ├── app/Http/Controllers/Api/
│       ├── app/Models/
│       ├── database/migrations/
│       ├── database/seeders/
│       └── routes/api.php
├── frontend/
│   └── vue-project/
│       ├── src/components/
│       ├── src/views/
│       ├── src/router/
│       └── src/services/
├── README.md
└── README.txt
```

---

## Prasības

| Rīks | Versija |
| --- | --- |
| Node.js | 20.19.0 vai jaunāka |
| npm | aktuāla stabilā versija |
| PHP | 8.2 vai jaunāka |
| Composer | aktuāla stabilā versija |
| Datubāze | MySQL vai cita Laravel konfigurācijā norādīta DB |

---

## Uzstādīšana

### 1. Backend sagatavošana

```bash
cd backend/laravel-app
composer install
cp .env.example .env
php artisan key:generate
```

### 2. Datubāzes konfigurēšana

Failā `backend/laravel-app/.env` norādi datubāzes pieslēguma datus:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=seekino
DB_USERNAME=root
DB_PASSWORD=
```

### 3. Migrācijas un sākuma dati

```bash
php artisan migrate
php artisan db:seed
```

Seeders izveido lietotājus, filmas, žanrus, zāles, sēdvietas, seansus un atsauksmes.

### 4. Frontend sagatavošana

```bash
cd ../../frontend/vue-project
npm install
```

### 5. API adreses konfigurācija

Ja nepieciešams, izveido `frontend/vue-project/.env` failu:

```env
VITE_API_BASE_URL=http://127.0.0.1:8000
```

---

## Palaišana

### Backend serveris

```bash
cd backend/laravel-app
php artisan serve
```

Laravel API pēc noklusējuma būs pieejams:

```text
http://127.0.0.1:8000
```

### Frontend serveris

```bash
cd frontend/vue-project
npm run dev
```

Vue lietotne parasti būs pieejama:

```text
http://localhost:5173
```

---

## Testa lietotāji

Pēc seeders palaišanas pieejami šādi lietotāji:

| Loma | E-pasts | Parole |
| --- | --- | --- |
| Lietotājs | `janis@example.com` | `password` |
| Administrators | `anna@example.com` | `password` |

---

## API maršruti

### Autentifikācija

| Metode | Maršruts |
| --- | --- |
| POST | `/api/register` |
| POST | `/api/login` |
| GET | `/api/me` |
| POST | `/api/logout` |
| PUT | `/api/profile` |

### Filmas un seansi

| Metode | Maršruts |
| --- | --- |
| GET | `/api/movies` |
| GET | `/api/movies/{movie}` |
| GET | `/api/movies/{movie}/feedbacks` |
| POST | `/api/movies/{movie}/feedbacks` |
| GET | `/api/screenings` |
| GET | `/api/screenings/{screening}` |

### Rezervācijas

| Metode | Maršruts |
| --- | --- |
| POST | `/api/reservations` |
| GET | `/api/profile/reservations` |
| PATCH | `/api/profile/reservations/{reservation}/cancel` |

### Administrēšana

| Metode | Maršruts |
| --- | --- |
| GET | `/api/admin/movies` |
| POST | `/api/admin/movies` |
| PUT | `/api/admin/movies/{movie}` |
| DELETE | `/api/admin/movies/{movie}` |
| GET | `/api/admin/screenings` |
| POST | `/api/admin/screenings` |
| PUT | `/api/admin/screenings/{screening}` |
| DELETE | `/api/admin/screenings/{screening}` |
| GET | `/api/admin/users` |
| PUT | `/api/admin/users/{user}` |
| DELETE | `/api/admin/users/{user}` |

---

## Build un testi

### Frontend produkcijas build

```bash
cd frontend/vue-project
npm run build
```

### Frontend build priekšskatījums

```bash
npm run preview
```

### Backend testi

```bash
cd backend/laravel-app
php artisan test
```

---

## Piezīmes

- Frontend pēc noklusējuma pieslēdzas API adresei `http://127.0.0.1:8000`.
- Administratora sadaļas paredzētas lietotājiem ar `admin` lomu.
- Ja datubāze ir tukša, vispirms jāpalaiž migrācijas un seeders.
- Rezerves datubāzes fails atrodas `backend/laravel-app/seekino_backup.sql`.
