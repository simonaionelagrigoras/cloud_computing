<!DOCTYPE html>
<html lang="en">
<head>
    <title>View Logs</title>
    <script type="text/javascript" src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"></link>

    <!-- Optional theme -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css"></link>


    <link rel="stylesheet" href="static/css/style.css"></link>

    <!-- Latest compiled and minified JavaScript -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>

</head>
<?php require_once('app/agreggateLogs.php');
$totalTime = 0;
?>

<body>
<div id="header">
</div>
<div id="menu">
    <nav class="navbar navbar-inverse">
        <div class="container">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse"
                        data-target=".navbar-collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="#">Logs-Reader</a>
            </div>
            <div class="navbar-collapse collapse">
                <ul class="nav navbar-nav">
                    <li class="active"><a href="http://localhost/cc/homework_1/">Home</a></li>
                    <li><a href="http://localhost/cc/homework_1/viewLogs.php">View Logs</a></li>
                    <li class="dropdown" style="float:right">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true"
                           aria-expanded="false"><span class="carety" id="user-account"></span></a>
                        <ul class="dropdown-menu">
                            <li><a href="#">Login</a></li>
                            <li><a href="#">Registration</a></li>
                            <li role="separator" class="divider"></li>
                            <li><a href="#">Separated link</a></li>
                            <li><a href="#">One more separated link</a></li>
                        </ul>
                    </li>
                </ul>
            </div><!--/.nav-collapse -->
        </div>
    </nav>
</div>
<div class="container">

    <div class="main">
        <div class="row">
            <table id="logs">
                <thead>
                    <tr>
                        <td>Date</td>
                        <td>Request key <pre class="no-style">Data</pre></td>
                    </tr>
                </thead>
                <?php foreach ($lines as $date => $dataLog):?>
                    <tr>
                        <td class="date-log"><strong><?php echo $date?></strong></td>
                        <td class="log-details">
                            <?php foreach ($dataLog as $key => $value):?>
                                <div class="log-line"><strong><?php echo $key?></strong>
                                <?php if(is_array($value)):?>
                                    <pre><?php echo json_encode($value, JSON_PRETTY_PRINT)?></pre>
                                <?php else:?>
                                    <pre class="no-style"><?php echo $value?></p></pre>
                                <?php endif?></div>
                                <?php if ($key == 'time'){
                                    $totalTime = $totalTime + floatval($value);
                                   // var_dump($value);
                                   // var_dump($totalTime);
                                }

                                ?>
                            <?php endforeach;?>

                        </td>
                    </tr><?php //break;?>
                <?php endforeach;?>
            </table>

        </div>
        <div id="top"><strong>Total Time: </strong><?php echo $totalTime?>&#09;
            <strong>Total Requests: </strong> <?php echo count($lines)?>&#09;
            <strong>Average time: </strong> <?php echo $totalTime/count($lines)?></div>
    </div>
    <footer class="footer-bs">
        <div class="row">
            <div class="col-md-3 footer-brand animated fadeInLeft">
                <h2>Logo</h2>
                <p>Some details about game</p>
                <p>© 2018 3B, All rights reserved</p>
            </div>
            <div class="col-md-4 footer-nav animated fadeInUp">
                <div class="col-md-6">
                    <ul class="list">
                        <li><a href="#">About Us</a></li>
                        <li><a href="#">Contacts</a></li>
                        <li><a href="#">Terms &amp; Condition</a></li>
                        <li><a href="#">Privacy Policy</a></li>
                    </ul>
                </div>
            </div>
            <div class="col-md-2 footer-social animated fadeInDown">
                <h4>Follow Us</h4>
                <ul>
                    <li><a href="#">Facebook</a></li>
                    <li><a href="#">Twitter</a></li>
                    <li><a href="#">Instagram</a></li>
                </ul>
            </div>
            <div class="col-md-3 footer-ns animated fadeInRight">
                <h4>Newsletter</h4>
                <p>Please subscribe for news</p>
                <p>
                </p><div class="input-group">
                    <input type="text" class="form-control" placeholder="Email address">
                    <span class="input-group-btn">
                        <button class="btn btn-default" type="button"><span class="glyphicon glyphicon-envelope"></span></button>
                      </span>
                </div><!-- /input-group -->
                <p></p>
            </div>
        </div>
    </footer>
</div>
</body>
</html>