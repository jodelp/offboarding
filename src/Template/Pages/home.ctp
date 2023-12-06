<?php
$this->layout = false;
?>
<!DOCTYPE html>
<html>
<head>
    <?= $this->Html->charset() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CS Staff Offboarding</title>

  <link href='http://fonts.googleapis.com/css?family=Roboto:5bold' rel='stylesheet' type='text/css'>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css"/>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css"/>

    <style>
    html{
      display:table;
      height:100%;
      width:100%}
    body{display:table-cell;
      font-family: 'Roboto';font-size: 20px;
      overflow-x: hidden;
      vertical-align:middle}.landingdiv{
        margin-left:auto;
        margin-right:auto;
        text-align:center}.landingdiv-body{
          margin-bottom: 10px;
        }
    ul li {
      width:2em;
      float:left;
      }
    </style>
</head>
<body style="background-color: #eceff1;font-family: 'Roboto', sans-serif;">
    <div class="landingdiv">
        <div class="landingdiv-body">
            <img src="/img/cslogo.png" style="margin-bottom:20px" alt="Cloustaff Logo" />
            <h1 class="landingdiv-heading" style="color: #0088d1; font-size:36px">CS Staff Offboarding</h1><br>
            <!-- <a class="btn btn-primary btn-sm" href="/admin" role="button" aria-pressed="true">LOG-IN</a> -->
        </div>
        <div class="landingdiv-footer" style="display: inline-block; ">
            <ul class="list-inline" >
                <li>
                    <a class="link-muted" href="https://twitter.com/Cloud_Staff" target="_blank">
                        <i class="fa fa-twitter" style="font-size: 25px;color:#757575"></i>
                    </a>
                </li>
                <li>
                    <a class="link-muted" href="https://www.facebook.com/Cloudstaff" target="_blank">
                        <i class="fa fa-facebook-square" style="font-size: 25px;color:#757575"></i>
                    </a>
                </li>
                <li>
                    <a class="link-muted" href="http://www.linkedin.com/company/cloudstaff" target="_blank">
                          <i class="fa fa-linkedin" style="font-size: 25px;color:#757575"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>
    <div class="row">
      <div class="col-md-12">
        <footer class="footer">
            <div class="container">
                <p style=" max-height: none;text-align:center;color:#757575; font-size:12px">
                    &copy;
                    <script type="text/javascript">
                        document.write(new Date().getFullYear());
                    </script>
                    Cloudstaff Philippines <br><br>
                    <b><?=$buildVersion?></b>
                </p>
            </div>
        </footer>
    </div>
</body>
</html>
