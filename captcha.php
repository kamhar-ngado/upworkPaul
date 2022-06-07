<?php
require_once "Mail.php";
// *** The CAPTCHA comparison - http://frikk.tk ***
// *** further modifications - http://www.captcha.biz ***

session_start();

// *** We need to make sure theyre coming from a posted form -
//	If not, quit now ***
if ($_SERVER["REQUEST_METHOD"] <> "POST")
    die("You can only reach this page by posting from the html form");

$email = $_POST["email"];
$name = $_POST["name"];
$phone = $_POST["phone"];
$comments = $_POST["comments"];

if (str_replace(" ", "", $name) == ""
    || str_replace(" ", "", $email) == ""
    || str_replace(" ", "", $phone) == ""
    || str_replace(" ", "", $comments) == ""
) {
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}

if (!validateEMAIL($email)) {
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}

//emailChecker($email);
function emailChecker($email)
{
    $url = "apilayer.net/api/check?access_key=f9ec13b0ee1da40027adfd2fc03df543&email=" . urlencode($email) . "&smtp=1&format=1";
    $service_url = $url;
    $curl = curl_init($service_url);
    $curl_post_data = array();

    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $curl_post_data);
    $curl_response = curl_exec($curl);
    curl_close($curl);
    $response = json_decode($curl_response);

    var_dump($response);
    die();
}

// *** The text input will come in through the variable $_POST["captcha_input"],
//	while the correct answer is stored in the cookie $_COOKIE["pass"] ***
if ($_POST["captcha_input"] == $_SESSION["pass"]) {
    // *** They passed the test! ***
    // *** This is where you would post a comment to your database, etc ***
    echo "<div class=\"container\" align=\"left\">";
    echo "<img src=\"img/logo.png\">";
    echo "</div>";
    echo "<div align:center style=\"background:#333333; text-align:center; margin:150 auto; font-size:24px; font-family:\'Lato\', sans-serif; padding:10px; color:#fff\"><br>";

//sends email via SMTP to the following address
    $to = "info@example.com";
    $from = "Messages <notifications@pwebco.ca>";
    $subject = "Online Quote Form";
    $host = "localhost";
    $port = "25";
    $username = '';
    $password = '';

    $headers = array('From' => $from,
        'To' => $to,
        'Subject' => $subject,
        'Reply-To' => $email,
        'Content-Type' => 'text/plain; charset=ISO-2022-JP');
    $body = '
	Name: ' . $_POST[name] . '
	Email: ' . $_POST[email] . '
	Phone: ' . $_POST[phone] . '
	Comments: ' . $_POST[comments] .'';

    $smtp = Mail::factory('smtp',
        array('host' => $host,
            'port' => $port,
            'auth' => false,
            'username' => $username,
            'password' => $password));

    //For Honeypot
    $date_of_birth = $_POST["date_of_birth"];
    $age = $_POST["age"];
    if (!empty($date_of_birth) || !empty($age)) {
        echo "Error!<br><br>";
        die("Bot Detected!");
    } else {
        $mail = $smtp->send($to, $headers, $body);

        if (PEAR::isError($mail)) {
            echo("<p>" . $mail->getMessage() . "</p>");
        } else {
            echo "Thank you for submitting your information! <br><br>";
            echo "One of our representatives will contact you as soon as possible.<br><br>";
            echo "This is what you sent  <br><br>";
            echo "Your Name: \"" . $_POST["name"] . "\" <br>";
            echo "Your email: \"" . $_POST["email"] . "\" <br>";
            echo "Your Telephone: \"" . $_POST["phone"] . "\" <br>";
            echo "Your Comments: \"" . $_POST["comments"] . "\" <br>";
            echo '<br><a href="index.html">Click Here</a> to return to main website';
            echo "</div>";
        }
    }
} else {
    // *** The input text did not match the stored text, so they failed ***
    // *** You would probably not want to include the correct answer, but
    //	unless someone is trying on purpose to circumvent you specifically,
    //	its not a big deal.  If someone is trying to circumvent you, then
    //	you should probably use a more secure way besides storing the
    //	info in a cookie that always has the same name! :) ***
    echo "Sorry, you did not pass the CAPTCHA test.<br><br>";
    echo "You said " . $_POST["captcha_input"];
    echo " while the correct answer was " . $_SESSION["pass"];
    echo "    - Please click back in your browser and try again <br><br>";
}

function validateEMAIL($email)
{
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
        return true;
    }
    return false;
}