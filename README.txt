Restaurant Inventory Request + Stock Monitoring System

PHP/Web: Web-Based Restaurant Inventory Request System
C# Windows Forms: Restaurant Inventory Stock Monitoring System
Database: SQLite
Connection: C# connects to PHP through api.php

HOW TO RUN
1. Open the php-api folder in Visual Studio Code.
2. Start the PHP server:
   php -S localhost:8000
3. Open the web system in your browser:
   http://localhost:8000/index.php
4. Add inventory requests using the PHP web form.
5. Open csharp-winforms/RestaurantInventoryStockMonitoringSystem.sln in Visual Studio.
6. Build and run the Windows Forms application.
7. Click Refresh Requests to load data from the PHP API.

API TEST URL
http://localhost:8000/api.php?action=list

IMPORTANT
The PHP server must be running before the C# system can load data.
