@echo off
echo ********************************
echo * UCRD Management System Setup *
echo ********************************
echo.
echo Starting UCRD Management System on http://localhost:9000
echo Using SQLite database (no MySQL server needed)
echo Press Ctrl+C to stop the server
echo.
php -S localhost:9000
pause 