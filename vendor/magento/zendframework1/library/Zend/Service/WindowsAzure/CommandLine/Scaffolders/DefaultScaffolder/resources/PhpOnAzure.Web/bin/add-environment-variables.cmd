@echo off
ECHO "Adding extra environment variables..." >> ..\startup-tasks-log.txt

powershell.exe Set-ExecutionPolicy Unrestricted
powershell.exe .\add-environment-variables.ps1 >> ..\startup-tasks-log.txt 2>>..\startup-tasks-error-log.txt

ECHO "Added extra environment variables." >> ..\startup-tasks-log.txt