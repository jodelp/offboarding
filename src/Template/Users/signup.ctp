<link href="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
<script src="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<?= $this->Html->css('signup.css') ?>
<!------ Include the above in your HEAD tag ---------->

<!DOCTYPE html>
<!--[if lt IE 7 ]> <html lang="en" class="no-js ie6 lt8"> <![endif]-->
<!--[if IE 7 ]>    <html lang="en" class="no-js ie7 lt8"> <![endif]-->
<!--[if IE 8 ]>    <html lang="en" class="no-js ie8 lt8"> <![endif]-->
<!--[if IE 9 ]>    <html lang="en" class="no-js ie9"> <![endif]-->
<!--[if (gt IE 9)|!(IE)]><!--> <html lang="en" class="no-js"> <!--<![endif]-->
    <head>
        <meta charset="UTF-8" />
        <!-- <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">  -->
        <title>Staff Offboarding</title>
		<link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" />
		<link href="https://fonts.googleapis.com/css?family=Raleway:100,100i,200,200i,300,300i,400,400i,500,500i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
		
    </head>
    <body>
        <div class="container">
            <section>				
                <div id="container_demo" style="margin-top:150px !important;overflow: hidden !important;">
                    <a class="hiddenanchor" id="toregister"></a>
                    <a class="hiddenanchor" id="tologin"></a>
                    
                    <div id="wrapper">
                        <div id="login" class="animate form">
                        <?= $this->Form->create(null, ['url' => ['controller' => 'Users', 'action' => 'login']]); ?>
                                <?= $this->Html->image('cloudstaff-logo-tag-landscape.png', ['alt' => 'CS Logo', 'style' => 'width:300px;margin-bottom: 20px;']) ?>
                                <?= $this->Flash->render() ?>
                                <p> 
                                    <label for="username" class="uname" > Your email </label>
                                    <input id="username" name="username" required="required" type="text" placeholder="mymail@mail.com"/>
                                </p>
                                <p> 
                                    <label for="otp" class="yourotp"> Password </label>
                                    <input id="otp" name="otp" required="required" type="password" placeholder="OTP / Password" /> 
                                </p>
                                <p class="signin button"> 
                                    <button type="submit" class="btn btn-primary-round tx-white" id="sendOTP">Login</button> 
								</p>
                                <p class="change_link">
									<a href="#toregister" class="to_register">Join us</a>
								</p>
                            <?= $this->Form->end() ?>
                        </div>

                        <div id="register" class="animate form">
                            <?= $this->Form->create($user) ?>
                                <?= $this->Html->image('cloudstaff-logo-tag-landscape.png', ['alt' => 'CS Logo', 'style' => 'width:300px;margin-bottom: 20px;']) ?>
                                <p> 
                                    <label for="username" class="uname" >Your email</label>
                                    <input id="username" name="username" required="required" type="text" placeholder="myuser@test.com" />
                                </p>
                                <p> 
                                    <label for="employee_id" class="youemployee_id"  > Your CSID</label>
                                    <input id="employee_id" name="employee_id" required="required" type="text" placeholder="e.g CSXXXX"/> 
                                </p>
                                <p class="signin button"> 
                                    <button type="submit" class="btn btn-primary-round tx-white" id="sendOTP">Send OTP</button> 
								</p>
                                <p class="change_link">  
									Already a member ?
									<a href="#tologin" class="to_register"> Go and log in </a>
								</p>
                            <?= $this->Form->end() ?>
                        </div>
						
                    </div>
                </div>  
            </section>
        </div>
    </body>
</html>