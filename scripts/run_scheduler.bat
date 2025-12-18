@echo off
:loop
echo Running Scheduler...
php c:\laragon\www\tugasAkhir\web\scripts\scheduler.php
echo Waiting 10 minutes...
timeout /t 600
goto loop
