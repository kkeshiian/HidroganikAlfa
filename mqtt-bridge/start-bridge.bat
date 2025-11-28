@echo off
echo ========================================
echo   Hidroganik MQTT Bridge
echo   Starting background service...
echo ========================================
echo.

cd /d "%~dp0"

echo Checking Node.js...
node --version >nul 2>&1
if errorlevel 1 (
    echo [ERROR] Node.js tidak ditemukan!
    echo Silakan install Node.js dari https://nodejs.org
    pause
    exit /b 1
)

echo Node.js OK
echo.

echo Checking dependencies...
if not exist "node_modules\" (
    echo Installing dependencies...
    call npm install
    if errorlevel 1 (
        echo [ERROR] Gagal install dependencies
        pause
        exit /b 1
    )
)

echo Dependencies OK
echo.

echo Starting MQTT Bridge...
echo Press Ctrl+C to stop
echo.
echo ========================================
echo   Service Running
echo   MQTT: broker.emqx.io:8084
echo   API: http://192.168.100.87
echo ========================================
echo.

node ingest.js

pause
