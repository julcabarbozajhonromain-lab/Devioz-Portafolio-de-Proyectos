@echo off
title Devioz 24 - Servidor de Desarrollo Local
echo =======================================================
echo   Iniciando Devioz 24 en http://127.0.0.1:8000
echo   Presiona Ctrl + C para detener el servidor
echo =======================================================
C:\xampp\php\php.exe -S 127.0.0.1:8000 -t frontend/public backend/bin/dev-router.php
pause
