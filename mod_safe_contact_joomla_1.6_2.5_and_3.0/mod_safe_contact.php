<?php

// no direct access
defined('_JEXEC') or die('Restricted access');

require_once ("recaptchalib.php");

//Email Parameters
$recipient = $params->get('email_recipient', '');
$fromName = @$params->get('from_name', 'Safe Contact');
$fromEmail = @$params->get('from_email', 'safe_contact@yoursite.com');

// Text Parameters
$myNameLabel = $params->get('name_label', 'Name');
$myEmailLabel = $params->get('email_label', 'Email *');
$mySubjectLabel = $params->get('subject_label', 'Subject');
$myMessageLabel = $params->get('message_label', 'Message *');
$buttonText = $params->get('button_text', 'Send Message');
$pageText = $params->get('page_text', 'Thank you for your contact.');
$errorText = $params->get('error_text', 'Your message could not be sent. Please try again.');
$noEmail = $params->get('no_email', 'Please write your email!');
$invalidEmail = $params->get('invalid_email', 'Please write a valid email!');
$noMessage = $params->get('no_message', 'Please write your message!');
$wrongreCaptcha = $params->get('wrong_recaptcha', 'The reCAPTCHA wasn\'t entered correctly. Try it again.');
$pre_text = $params->get('pre_text', '');

// Size and Color Parameters
$thanksTextColor = $params->get('thank_text_color', '#FF0000');
$error_text_color = $params->get('error_text_color', '#FF0000');
$labelColor = $params->get('label_color', '#BCBCBC');
$textColor = $params->get('text_color', '#000000');
$inputWidth = $params->get('input_width', '176');
$textareaWidth = $params->get('textarea_width', '174');
$recaptchaWidth = $params->get('recaptcha_width', '178');
$buttonWidth = $params->get('button_width', '100');
$addcss = $params->get('addcss', 'div.safe_contact tr, div.safe_contact td { border: none; padding: 3px; }');

// URL Parameters
$exact_url = $params->get('exact_url', true);
$disable_https = $params->get('disable_https', true);
$fixed_url = $params->get('fixed_url', true);
$myFixedURL = $params->get('fixed_url_address', '');

// reCaptcha Parameters
$enable_recaptcha = $params->get('enable_recaptcha', true);
$recaptcha_private_key = $params->get('private_key', '6Leha8QSAAAAAOyekb4TZpIyXpPlmyhqhYUlj79m');
$recaptcha_public_key = $params->get('public_key', '6Leha8QSAAAAAGo3GXc6ce6ESObwbOqi3WBTq2KN');

// Module Class Suffix Parameter
$mod_class_suffix = $params->get('moduleclass_sfx', '');


if ($fixed_url)
{
    $url = $myFixedURL;
}
else
{
    if (!$exact_url)
    {
        $url = JURI::current();
    }
    else
    {
        if (!$disable_https)
        {
            $url = (!empty($_SERVER['HTTPS'])) ? "https://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'] : "http://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
        }
        else
        {
            $url = "http://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
        }
    }
}

$url = htmlentities($url, ENT_COMPAT, "UTF-8");

$myError = '';
$CORRECT_NAME = $myNameLabel;
$CORRECT_EMAIL = $myEmailLabel;
$CORRECT_SUBJECT = $mySubjectLabel;
$CORRECT_MESSAGE = $myMessageLabel;

if (isset($_POST["sc_email"]))
{
    $CORRECT_NAME = htmlentities($_POST["sc_name"], ENT_COMPAT, "UTF-8");
    $CORRECT_SUBJECT = htmlentities($_POST["sc_subject"], ENT_COMPAT, "UTF-8");

    // check email
    if ($_POST["sc_email"] === "")
    {
        $myError .= '<span style="color: ' . $error_text_color . ';"> - ' . $noEmail . '</span><br/>';
    }
    if (!preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/", strtolower($_POST["sc_email"])))
    {
        $myError .= '<span style="color: ' . $error_text_color . ';"> - ' . $invalidEmail . '</span><br/>';
    }
    else
    {
        $CORRECT_EMAIL = htmlentities($_POST["sc_email"], ENT_COMPAT, "UTF-8");
    }

    // check message
    if ($_POST["sc_message"] === $myMessageLabel || $_POST["sc_message"] === "")
    {
        $myError .= '<span style="color: ' . $error_text_color . ';"> - ' . $noMessage . '</span><br/>';
    }
    else
    {
        $CORRECT_MESSAGE = htmlentities($_POST["sc_message"], ENT_COMPAT, "UTF-8");
    }

    // check reCaptcha
    if ($enable_recaptcha)
    {        
        $resp = recaptcha_check_answer
        (
            $recaptcha_private_key, $_SERVER["REMOTE_ADDR"], $_POST["recaptcha_challenge_field"], $_POST["recaptcha_response_field"]
        );

        if (!$resp->is_valid)
        {
            $myError .= '<span style="color: ' . $error_text_color . ';"> - ' . $wrongreCaptcha . '</span><br/>';
        }
    }

    if ($myError == '')
    {
        $mySubject = $_POST["sc_subject"];
        $myMessage = "You received a message from \n" . $_POST["sc_name"] . " (" . $_POST["sc_email"] . ")\n\n" . $_POST["sc_message"];

        $mailSender = &JFactory::getMailer();
        $mailSender->addRecipient($recipient);

        $mailSender->setSender(array($fromEmail, $fromName));
        $mailSender->addReplyTo(array($_POST["sc_email"], ''));

        $mailSender->setSubject($mySubject);
        $mailSender->setBody($myMessage);

        if ($mailSender->Send() !== true)
        {
            $myReplacement = '<span style="color: ' . $error_text_color . ';">' . $errorText . '</span>';
            print $myReplacement;
            return true;
        }
        else
        {
            $myReplacement = '<span style="color: ' . $thanksTextColor . ';">' . $pageText . '</span>';
            print $myReplacement;
            return true;
        }
    }
} // end if posted
// check recipient
if ($recipient === "")
{
    $myReplacement = '<span style="color: ' . $error_text_color . ';">No recipient specified</span>';
    print $myReplacement;
    return true;
}

print '<style type="text/css"><!--' . $addcss . '--></style>';
print '<div class="safe_contact ' . $mod_class_suffix . '"><form action="' . $url . '" method="post">' . "\n" .
        '<div class="safe_contact intro_text ' . $mod_class_suffix . '">' . $pre_text . '</div>' . "\n";

if ($myError != '')
{
    print $myError;
}


print '<table>';

// print name input
print '<tr><td><input class="safe_contact inputbox ' . $mod_class_suffix . '" type="text" name="sc_name" value="' . $CORRECT_NAME . '" ';
if ($CORRECT_NAME == $myNameLabel)
    print 'style="color:' . $labelColor . '; width:' . $inputWidth . 'px"';
print 'onblur="if(this.value==\'\') {this.value=\'' . $myNameLabel . '\';this.style.color=\'' . $labelColor . '\'}" onfocus="if(this.value==\'' . $myNameLabel . '\') {this.value=\'\';this.style.color=\'' . $textColor . '\'}"/></td></tr>' . "\n";
// print email input
print '<tr><td><input class="safe_contact inputbox ' . $mod_class_suffix . '" type="text" name="sc_email" value="' . $CORRECT_EMAIL . '" ';
if ($CORRECT_EMAIL == $myEmailLabel)
    print 'style="color:' . $labelColor . '; width:' . $inputWidth . 'px"';
print 'onblur="if(this.value==\'\') {this.value=\'' . $myEmailLabel . '\';this.style.color=\'' . $labelColor . '\'}" onfocus="if(this.value==\'' . $myEmailLabel . '\') {this.value=\'\';this.style.color=\'' . $textColor . '\'}"/></td></tr>' . "\n";
// print subject input
print '<tr><td><input class="safe_contact inputbox ' . $mod_class_suffix . '" type="text" name="sc_subject" value="' . $CORRECT_SUBJECT . '" ';
if ($CORRECT_SUBJECT == $mySubjectLabel)
    print 'style="color:' . $labelColor . '; width:' . $inputWidth . 'px"';
print 'onblur="if(this.value==\'\') {this.value=\'' . $mySubjectLabel . '\';this.style.color=\'' . $labelColor . '\'}" onfocus="if(this.value==\'' . $mySubjectLabel . '\') {this.value=\'\';this.style.color=\'' . $textColor . '\'}"/></td></tr>' . "\n";
// print message input
print '<tr><td valign="top"><textarea class="safe_contact textarea ' . $mod_class_suffix . '" name="sc_message" rows="4"';
if ($CORRECT_MESSAGE == $myMessageLabel)
    print 'style="color:' . $labelColor . '; width:' . $textareaWidth . 'px"';
print 'onblur="if(this.value==\'\') {this.value=\'' . $myMessageLabel . '\';this.style.color=\'' . $labelColor . '\'}" onfocus="if(this.value==\'' . $myMessageLabel . '\') {this.value=\'\';this.style.color=\'' . $textColor . '\'}">' . $CORRECT_MESSAGE . '</textarea></td></tr>' . "\n";


//print recaptcha
if ($enable_recaptcha)
{
    print '<tr><td colspan="2">
    <script type="text/javascript">        
        var RecaptchaOptions = {
            theme: \'custom\',
            custom_theme_widget: \'recaptcha_widget\'
        };                                                        
    </script>
    <style type="text/css">                
        #recaptcha_image img { 
            width: ' . $recaptchaWidth . 'px; 
        }
        #recaptcha_image{        
            width: ' . $recaptchaWidth . 'px; 
            border: 1px solid gainsboro;
            padding: 6px 0px;
        }        
    </style>    
    <div id="recaptcha_widget" style="display:none">

   <div id="recaptcha_image"></div>
   <div class="recaptcha_only_if_incorrect_sol" style="color:red">Incorrect please try again</div>
<!--
   <span class="recaptcha_only_if_image">Enter the words above:</span>
   <span class="recaptcha_only_if_audio">Enter the numbers you hear:</span>
-->
   <input type="text" id="recaptcha_response_field" name="recaptcha_response_field" style="width:' . ($recaptchaWidth - 21) . 'px"/>

   <div style="display:inline;"><a href="javascript:Recaptcha.reload()"><img src="modules/mod_safe_contact/images/refresh-captcha.png" style="position:relative;top:3px;"/></a></div>
   <!--<div class="recaptcha_only_if_image"><a href="javascript:Recaptcha.switch_type(\'audio\')">Get an audio CAPTCHA</a></div>
   <div class="recaptcha_only_if_audio"><a href="javascript:Recaptcha.switch_type(\'image\')">Get an image CAPTCHA</a></div>

   <div><a href="javascript:Recaptcha.showhelp()">Help</a></div> -->

 </div>

 <script type="text/javascript"
    src="http://www.google.com/recaptcha/api/challenge?k=' . $recaptcha_public_key . '">
 </script>
 <noscript>
   <iframe src="http://www.google.com/recaptcha/api/noscript?k=' . $recaptcha_public_key . '"
        height="300" width="500" frameborder="0"></iframe><br>
   <textarea name="recaptcha_challenge_field" rows="3" cols="40">
   </textarea>
   <input type="hidden" name="recaptcha_response_field"
        value="manual_challenge">
 </noscript>
 <script type="text/javascript">        
    document.getElementById(\'recaptcha_image\').style.width = \'' . $recaptchaWidth . 'px\';
 </script> 
</td></tr>' . "\n";
}

// print button
print '<tr><td colspan="2"><input class="safe_contact button ' . $mod_class_suffix . '" type="submit" value="' . $buttonText . '" style="width: ' . $buttonWidth . '%"/></td></tr></table></form></div>' . "\n";
return true;
