<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>email notification template</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="">
<meta name="author" content="">
<style type="text/css">

html {
font-size: 100%;
}

.container-background {
background-color: #f5f5f5;
font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
padding: 20px 50px;
}

a {
color: #e8112d;
text-decoration: none;
}
a:hover, a:focus {
columns: #e8112d;
text-decoration:underline;
}

.container-fluid {
padding-right: 20px;
padding-left: 20px;
*zoom: 1;
}

.container-fluid:before,
.container-fluid:after {
display: table;
content: "";
line-height: 0;
}

.container-fluid:after {
clear: both;
}

.hero-unit {
background-color: #ffffff;
border: 1px solid #cccccc;
border-radius: 6px;
-webkit-border-radius: 6px;
-moz-border-radius: 6px;
color: inherit;
padding: 5px 10px 5px 30px;
font-family: Arial, sans-serif;
font-size: 14px;
}

.hero-unit h1 {
margin-bottom: 0;
font-size: 60px;
line-height: 1;
color: inherit;
letter-spacing: -1px;
}

.hero-unit li {
line-height: 30px;
}

img {
border: 0 none;
height: auto;
max-width: 100%;
vertical-align: middle;
}

.head-container {
display: flex;
align-items: center;
background-color: #d9d9d9;
vertical-align: middle!important;
}

td {
vertical-align: center;
}

.container {
    margin: 0px 200px 0px 200px;
    width: auto;
}
</style>
</head>
<body>
    <div class="container">
        <div style="background-color: #bd241f; padding: 15px;">
            <div style="padding-left: 50px;" align="left"><img src="https://doa.az.gov/sites/default/files/ADOA-White-300px.png" width="200px" /></div>
            </div>
            <div class="container-background">
            <div style="padding: 10px;" align="center">
            <h3 style="color: #bd241f;"><strong>Remote Work Agreement</strong></h3>
            </div>
            <div class="container-fluid">
            <div class="hero-unit">
            <p>Hi <strong>{{{ $EMPLOYEE_FIRST_NAME }}} {{{ $EMPLOYEE_LAST_NAME }}}</strong>,</p>
            <p>Your current Remote Work Agreement <strong>#{{ $RWA_REQUEST_ID }}</strong> will expire on <strong>{{ $RWA_EXPIRATE_END_DATE }}</strong>.</p>
            <p>If you want to renew your agreement, please follow these steps:</p>
            <ol>
            <li>Click <a draggable="false" href="http://yesportal.azdoa.gov/" target="_blank" rel="noopener">here</a> to login to Y.E.S. <strong>(Do not use Internet Explorer)</strong></li>
            <li>Click the <strong>Remote Work Agreement</strong> bookmark</li>
            <li>Click the <strong>+START HERE</strong> button in the upper right corner</li>
            <li>In the EMPLOYEE SPACE menu click the <strong>START</strong> button for &ldquo;Remote Work - Initiate or Terminate Agreement&rdquo;</li>
            <li>If your RWA has not expired it will be loaded with the &ldquo;Terminate&rdquo; option selected. <strong> Click &ldquo;New&rdquo; and then confirm you want to renew your agreement.</strong> Make any changes to your information and click Submit.</li>
            <li>If your RWA has expired you will have to re-enter all your information and click Submit.</li>
            </ol>
            <p>Regards,<br /><em>ADOA Team</em></p>
            </div>
            </div>
        </div>
    </div>
</body>
</html>
