<?php
// API configuration file for BMS

// API rate limit settings
define('RATE_LIMIT', 100); // requests per window
define('RATE_LIMIT_WINDOW', 60); // seconds

// Allowed origins for CORS
define('ALLOWED_ORIGINS', ['http://localhost', 'http://localhost:3000', 'http://127.0.0.1']);

// Token secret key for hashing - should be 32+ characters
define('TOKEN_SECRET', 'bms_secure_token_key_2024_production_env_change_me');

// API token expiry time
define('API_TOKEN_EXPIRY', 86400); // 24 hours in seconds