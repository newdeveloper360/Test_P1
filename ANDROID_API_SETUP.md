# Android location API

The Laravel server should listen on port 8000. From this folder, run:

```powershell
php artisan migrate
php artisan serve --host=0.0.0.0 --port=8000
```

On this computer the API is `http://127.0.0.1:8000/api`. The updated APK uses `http://192.168.31.53:8000/api` on phone and emulator, and replaces older saved `10.0.2.2` addresses automatically; enter a different computer Wi-Fi IP in the app if it changes. Windows Firewall may need to allow PHP on the private network. A phone's `127.0.0.1` points to that phone, not this computer.

`POST /api/user/create` accepts name, phone, and password. `POST /api/login` accepts phone and password. Both return a bearer token. Authenticated routes are `GET /api/me`, `POST /api/logout`, `POST /api/locations`, `GET /api/locations/latest`, and `GET /api/locations`. Location fields are saved in separate columns with capture and server timestamps.

