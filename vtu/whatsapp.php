<?php
// chat-widget.php
// WhatsApp Chat Widget Implementation

// Configuration: Set your WhatsApp number (with country code, no + or spaces)
$whatsappNumber = '1234567890'; // Replace with your WhatsApp number

// Optional: Set a default message
$defaultMessage = urlencode('Hello! I would like to chat with you.');

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>WhatsApp Chat Widget</title>
    <style>
        #whatsapp-chat-widget {
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 9999;
        }
        #whatsapp-chat-widget a {
            display: flex;
            align-items: center;
            background: #25D366;
            color: #fff;
            padding: 12px 20px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: bold;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            transition: background 0.2s;
        }
        #whatsapp-chat-widget a:hover {
            background: #128C7E;
        }
        #whatsapp-chat-widget img {
            width: 28px;
            height: 28px;
            margin-right: 10px;
        }
        #whatsapp-fab {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, #25D366 60%, #128C7E 100%);
            border-radius: 50%;
            box-shadow: 0 4px 16px #25D366;
            z-index: 1100;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: box-shadow 0.2s;
            text-decoration: none;
        }
        #whatsapp-fab i {
            font-size: 2rem;
            color: #fff;
            text-shadow: 0 0 8px #25D366;
        }
    </style>
</head>
<body>
    <div id="whatsapp-chat-widget">
        <a href="https://wa.me/<?php echo $whatsappNumber; ?>?text=<?php echo $defaultMessage; ?>" target="_blank" rel="noopener">
            <img src="https://upload.wikimedia.org/wikipedia/commons/6/6b/WhatsApp.svg" alt="WhatsApp">
            Chat with us
        </a>
    </div>
    <!-- WhatsApp Floating Chat Button -->
    <a href="https://wa.me/2348012345678?text=Hello%20VTU%20Support%2C%20I%20need%20assistance." target="_blank" id="whatsapp-fab" style="position:fixed;bottom:30px;right:30px;width:56px;height:56px;background:linear-gradient(135deg,#25D366 60%,#128C7E 100%);border-radius:50%;box-shadow:0 4px 16px #25D366;z-index:1100;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:box-shadow 0.2s;text-decoration:none;">
        <i class="fab fa-whatsapp" style="font-size:2rem;color:#fff;text-shadow:0 0 8px #25D366;"></i>
    </a>
</body>
</html>