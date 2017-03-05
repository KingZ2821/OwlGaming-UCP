<?php
$to = '"Somename Lastname" <<a href="mailto:someone@email.com">someone@email.com</a>>';
$subject = 'PHP mail tester';
$message = 'This message was sent via PHP!' . PHP_EOL .
           'Some other message text.' . PHP_EOL . PHP_EOL .
           '-- signature' . PHP_EOL;
$headers = 'From: "From Name" <<a href="mailto:from@email.dom">from@email.dom</a>>' . PHP_EOL .
           'Reply-To: <a href="mailto:reply@email.com">reply@email.com</a>' . PHP_EOL .
           'Cc: "CC Name" <<a href="mailto:cc@email.dom">cc@email.dom</a>>' . PHP_EOL .
           'X-Mailer: PHP/' . phpversion();

if (mail($to, $subject, $message, $headers)) {
  echo 'mail() Success!';
}
else {
  echo 'mail() Failed!';
}
?>
