<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w31.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
    <style>
        body {
            background-color: #FFF; /*white*/

        }

        .wrapper_table {
            width: 90%;
            border-collapse: collapse;
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 10px auto;
            padding: 2px;
            background-color: #fff;
            border: 2px solid #0d83ab; /* med purple */
        }

        .header_td {
            /*background: #3B97D3;  med blue, a little lighter */
            background: #0d83ab;
            padding: 10px;
            
        }
        .header_a {
            color: #fff;
            text-decoration: none;
        }

        .message_td {
            padding: 15px 10px;
            line-height: 1.5em;
        }

        .footer_td {
            /*background: #34495E; /* grayish blue */
            padding: 20px 0;
            text-align: center;
            background:#313a45; 
        }

        .footer_p {
            letter-spacing: 2px;
        }

        .footer_a {
            text-decoration: none;
            color: #fff;
        }
        .message_tr{
            
        }

    </style>
</head>
<body>
<table class="wrapper_table">
    <tr>
        <td class="header_td">
            <h2><a  class="header_a" href="<?php echo base_url(); ?>">
                <?php /*<img src="<?php echo base_url('assets/img/email_header.png'); ?>" alt="logo" /> */ ?>
                <?php echo getenv('APP_NAME'); ?>
            </a></h2>
        </td>
    </tr>
    <tr class="message_tr">
        <td class="message_td"><?php echo $message; ?></td>
    </tr>
    <tr>
        <td class="footer_td">
            <p class="footer_p"><a class="footer_a" href="<?php echo base_url(); ?>"><?php echo getenv('BASE_URL'); ?></a></p>
        </td>
    </tr>
</table>
</body>
</html>
