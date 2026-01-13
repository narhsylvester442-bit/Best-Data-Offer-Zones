
<?php
$bot = getenv("8513882874:AAFm43QjYVMt3KsfE9l6tPp22qfluLu5-mw");
$update=json_decode(file_get_contents("php://input"),true);
$chat=$update["message"]["chat"]["id"];
file_get_contents("https://api.telegram.org/bot$bot/sendMessage?chat_id=$chat&text=Order received");
?>
