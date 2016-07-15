Cometchat-Pubnub-Transport-Files
================================
// Tested with Cometchat 4.4.0    

Requirements:

- Cometchat
- Pubnub Free account

Installation:

- I assume you already have cometchat installed.
- Copy this dir to cometchat/transports/ directory.
- Edit Config.php and fill in missing info.
- EDIT Config.php in BASE directory and fill in the missing info :

define('USE_COMET','0'); -> define('USE_COMET','1');    
define('SAVE_LOGS','0');    
define('COMET_HISTORY_LIMIT','100');    
define('KEY_A',''); -> define('KEY_A','SUBSCRIBE_KEY');     
define('KEY_B',''); -> define('KEY_B','PUBLISH_KEY');   
define('KEY_C','');    
   
- Reload webpage and enjoy!   
