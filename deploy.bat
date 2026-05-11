@echo off
cd /d %~dp0

echo Initializing Git...
git init

echo Setting main branch...
git branch -M main

echo Adding GitHub remote...
git remote remove origin >nul 2>&1
git remote add origin https://github.com/marekziak22/portfolio.git

echo Adding files...
git add .

echo Creating commit...
git commit -m "portfolio deploy"

echo Pushing to GitHub...
git push -u origin main

echo.
echo DONE!
echo.
echo Open:
echo https://github.com/marekziak22/portfolio/actions
pause