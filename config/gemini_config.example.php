<?php
// config/gemini_config.php example

/**
 * Google Gemini API Configuration
 * 
 * Instructions:
 * 1. Obtain an API key from Google AI Studio (https://aistudio.google.com/)
 * 2. Replace the placeholder below with your actual API key
 * 3. Do not commit your actual API key to version control
 */

define('GEMINI_API_KEY', 'YOUR_GEMINI_API_KEY_HERE');

define('GEMINI_MODELS', [
    'gemini-2.5-flash',
    'gemini-2.0-flash',
    'gemini-2.0-flash-lite',
    'gemini-1.5-flash'
]);
