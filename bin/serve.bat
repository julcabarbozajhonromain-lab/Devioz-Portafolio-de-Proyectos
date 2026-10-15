@echo off
title Devioz Classic — Servidor de Desarrollo Local
echo =======================================================
echo   Iniciando Devioz Classic en http://127.0.0.1:8000
echo   Presiona Ctrl + C para detener el servidor
echo =======================================================
C:\xampp\php\php.exe -S 127.0.0.1:8000 -t public
pause
