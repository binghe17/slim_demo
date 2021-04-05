<?php
    use \psr\http\message\ServerRequestInterface as Request;
    use \psr\http\message\ResponseInterface as Response;


    // $app = new \slim\app;
    // $config = ['settings' => [
    //     'addContentLengthHeader' => false,
    // ]];
    // $app = new \Slim\App($config);
    //$encToken = 13535325632;


    // $app->get('/test', function ($request, $response) {
    //     $params = $request->getQueryParams();
    //     echo $response->write("Hello " . var_dump($params));
    // });


    // post        /user/login
    // post        /user/register
    // post        /user/forgot
    // post        /user/forgot2
    // post        /admin/login
    // post        /admin/add
    // post        /admin/forgot
    // post        /admin/forgot2
    // post        /guest/login
    // post        /change/name
    // post        /change/pw
    // post        /credit/add
    // get     /user/show
    // post        /user/update
    // post        /user/delete
    // post        /user/add
    // get     /admin/show
    // post        /admin/update
    // post        /admin/delete
    // post        /admin/change/password
    // post        /admin/change/username
    // post        /admin/change/email
    // post        /admin/change/name
    // get     /item/show
    // post        /item/add
    // post        /item/delete
    // post        /item/update
    // get     /train/show
    // post        /train/search
    // post        /train/add
    // post        /train/delete
    // post        /train/update
    // get     /city/show
    // post        /seat/show
    // get     /time/show
    // post        /cart/show
    // post        /train/status
    // post        /ticket/purchase
    // post        /history/show
    // post        /history/delete
    // post        /history/delete/all
    // get     /system/history/delete
    // post        /history/ticket
    // post        /history/item
    // post        /item/buy
    // post        /add/time
    // post        /edit/time
    // post        /delete/time
    // post        /add/city
    // post        /edit/city
    // post        /delete/city
    // get     /show/category
    // get     /manage/train



    //user Login
    $app->post('/user/login', function(Request $request, Response $response){

        $username = $request->getParam('username');
        $password = $request->getParam('password');
        $psw = $password;
        $password = md5($password);
        $sql = "SELECT * FROM user WHERE username = :username AND password = :password";
        try {
            $db = new db();
            $db = $db->connect();
            $stmt = $db->prepare($sql);
            $stmt->bindParam(":username", $username);
            $stmt->bindParam(":password", $password);
            $stmt->execute();
            if($stmt->rowCount()==0){
                $data = array(
                    "message"   => "username atau password anda salah.",
                    "login"     => false
                );
                return json_encode($data);
            } else {
                $fetch = $stmt->fetch(PDO::FETCH_ASSOC);
                $name = $fetch['name'];
                $credit = intval($fetch['credit']);
                $username = $fetch['username'];
                $id = intval($fetch['id']);
                $data = array(
                    "message"   => "anda telah login sebagai ".$name,
                    "name"      => $name,
                    "username"  => $username,
                    "password"  => $psw,
                    "credit"    => $credit,
                    "login"     => true,
                    "id"        => $id
                );
                return json_encode($data);
            }
        } catch (PDOException $e) {
            $e->getMessage();
        }
    });

    $app->post('/user/register', function(Request $request, Response $response){

        $name       = $request->getParam('name');
        $username   = $request->getParam('username');
        $password   = $request->getParam('password');
        $password   = md5($password);
        $email      = $request->getParam('email');
        $credit     = 0;

        try {
            $db = new db();
            $db = $db->connect();

            $sql = "SELECT * FROM user WHERE username = :username";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(":username", $username);
            $stmt->execute();
            if($stmt->rowCount() > 0){
                $issetUser = false;
            } else {
                $issetUser = true;
            }

            $sql = "SELECT * FROM user WHERE email = :email";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(":email", $email);
            $stmt->execute();
            if($stmt->rowCount() > 0){
                $issetEmail = false;
            } else {
                $issetEmail = true;
            }

            if($issetUser == false){

                $data = array("message" => "username tersebut telah terdaftar");
            } else {
                if($issetEmail == false){

                    $data = array("message" => "email tersebut telah terdaftar");
                } else {

                    $sql = "INSERT INTO user (name, username, password, email, credit) 
                    VALUES (:name, :username, :password, :email, :credit)";

                    $stmt = $db->prepare($sql);
                    $stmt->bindParam(":name", $name);
                    $stmt->bindParam(":username", $username);
                    $stmt->bindParam(":password", $password);
                    $stmt->bindParam(":email", $email);
                    $stmt->bindParam(":credit", $credit);
                    $stmt->execute();

                    $data = array("message" => "Registrasi berhasil");
                }
            }

            return json_encode($data);

        } catch (PDOException $e){
            $e->getMessage();
        }
    });

    $app->post('/user/forgot', function(Request $request, Response $response){
        $username = $request->getParam('username');

        try{
            $db = new db();
            $db = $db->connect();

            $sql = "SELECT * FROM user WHERE username = :username";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(":username", $username);
            $stmt->execute();

            if($stmt->rowCount() == 0){
                $data = array("message" => "username tersebut tidak terdaftar");
            } else {
                $value = $stmt->fetch(PDO::FETCH_ASSOC);
                $email = $value['email'];
                $token = $value['id'];
                $token = $token * 13535325632;

                $from = "rbttbr222@gmail.com";
                $header = "From: ".$from;
                $to = $email;
                $subject = "Forgot R-Train Password";
                $message = "token anda adalah " . $token;
                mail($to, $subject, $message, $header);

                $data = array(
                    "message"   => "token akan dikirim di email anda",
                    "info"      => true
                );
            }

            return json_encode($data);

        } catch (PDOException $e){
            $e->getMessage();
        }
    });

    $app->post('/user/forgot2', function(Request $request, Response $response){
        $token = $request->getParam('token');
        $token = $token/13535325632;
        $password = $request->getParam('password');
        $password = md5($password);

        try {
            
            $db = new db();
            $db = $db->connect();
            
            $sql = "SELECT * FROM user WHERE id = :id";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(":id", $token);
            $stmt->execute();
            if($stmt->rowCount() == 0){
                $data = array("message" => "token anda salah");
            } else {
                $sql = "UPDATE user SET password = :password WHERE id = :id";
                $stmt = $db->prepare($sql);
                $stmt->bindParam(":password", $password);
                $stmt->bindParam(":id", $token);
                $stmt->execute();

                $data = array("message" => "password anda berhasil di ubah");
            }

            return json_encode($data);
        } catch (PDOException $e){
            $e->getMessage();
        }
    });
    //================================================================================
    
    //admin Login
    $app->post('/admin/login', function(Request $request, Response $response){

        $username = $request->getParam('username');
        $password = $request->getParam('password');
        $psw = $password;
        $password = md5($password);
        $sql = "SELECT * FROM admin WHERE username = :username AND password = :password";
        try {
            $db = new db();
            $db = $db->connect();
            $stmt = $db->prepare($sql);
            $stmt->bindParam(":username", $username);
            $stmt->bindParam(":password", $password);
            $stmt->execute();
            if($stmt->rowCount()==0){
                $data = array(
                    "message"   => "username atau password anda salah.",
                    "login"     => false
                );
            } else {
                $fetch = $stmt->fetch(PDO::FETCH_ASSOC);
                $username = $fetch['username'];
                $name = $fetch['name'];
                $email = $fetch['email'];
                $id = intval($fetch['id']);
                $data = array(
                    "message"   => "anda telah login sebagai admin",
                    "name"      => $name,
                    "username"  => $username,
                    "password"  => $password,
                    "email"     => $email,
                    "id"        => $id,
                    "login"     => true
                );
            }
            return json_encode($data);
        } catch (PDOException $e) {
            $e->getMessage();
        }
    });

    $app->post('/admin/add', function(Request $request, Response $response){
        $name = $request->getParam('name');
        $username = $request->getParam('username');
        $password = $request->getParam('password');
        $password = md5($password);
        $email = $request->getParam('email');

        try {

            $db = new db();
            $db = $db->connect();

            $sql = "SELECT * FROM admin WHERE username = :username";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(":username", $username);
            $stmt->execute();

            if($stmt->rowCount() > 0){
                $data = array(
                    "message"   => "username tersebut telah terdaftar",
                    "info"      => false
                );
            } else {
                $sql = "SELECT * FROM admin WHERE email = :email";
                $stmt = $db->prepare($sql);
                $stmt->bindParam(":email", $email);
                $stmt->execute();

                if($stmt->rowCount() > 0){
                    $data = array(
                        "message"   => "email tersebut telah terdaftar",
                        "info"      => false
                    );
                } else {
                    $sql = "INSERT INTO admin(name, username, password, email) VALUES(:name, :username, :password, :email)";
                    $stmt = $db->prepare($sql);
                    $stmt->bindParam(":name", $name);
                    $stmt->bindParam(":username", $username);
                    $stmt->bindParam(":password", $password);
                    $stmt->bindParam(":email", $email);
                    $stmt->execute();

                    $data = array(
                        "message"   => "berhasil tambah admin",
                        "info"      => true
                    );
                }
            }

            return json_encode($data);
        } catch (PDOException $e){
            return $e->getMessage();
        }
    });

    $app->post('/admin/forgot', function(Request $request, Response $response){
        $username = $request->getParam('username');

        try{
            $db = new db();
            $db = $db->connect();

            $sql = "SELECT * FROM admin WHERE username = :username";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(":username", $username);
            $stmt->execute();

            if($stmt->rowCount() == 0){
                $data = array("message" => "email tersebut tidak terdaftar");
            } else {
                $value = $stmt->fetch(PDO::FETCH_ASSOC);
                $email = $value['email'];
                $token = $value['id'];
                $token = $token * 13535325632;

                $from = "rbttbr222@gmail.com";
                $header = "From: ".$from;
                $to = $email;
                $subject = "Forgot R-Train Password";
                $message = "token anda adalah " . $token;
                mail($to, $subject, $message, $header);

                $data = array(
                    "message"   => "token akan dikirim di email anda",
                    "info"      => true
                );
            }

            return json_encode($data);

        } catch (PDOException $e){
            $e->getMessage();
        }
    });

    $app->post('/admin/forgot2', function(Request $request, Response $response){
        $token = $request->getParam('token');
        $token = $token/13535325632;
        $password = $request->getParam('password');
        $password = md5($password);

        try {
            
            $db = new db();
            $db = $db->connect();
            
            $sql = "SELECT * FROM admin WHERE id = :id";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(":id", $token);
            $stmt->execute();
            if($stmt->rowCount() == 0){
                $data = array("message" => "token anda salah");
            } else {
                $sql = "UPDATE user SET password = :password WHERE id = :id";
                $stmt = $db->prepare($sql);
                $stmt->bindParam(":password", $password);
                $stmt->bindParam(":id", $token);
                $stmt->execute();

                $data = array("message" => "password anda berhasil di ubah");
            }

            return json_encode($data);
        } catch (PDOException $e){
            $e->getMessage();
        }
    });
    //==================================================================================

    //guest Login
    $app->post('/guest/login', function(Request $request, Response $response){
        $email = $request->getParam('email');
        $name = $request->getParam('name');
        $id = crc32($email);
        $id /= 1230321;
        $id = strval($id);
        $id = explode(".", $id);
        $id = intval($id[0]);

        $from = "rbttbr222@gmail.com";
        $header = "From: ".$from;
        $to = $email;
        $subject = "Pemberitahuan login R-Train";
        $message = "id guest anda adalah " . $id . "\n" . 
                "gunakan selalu email ini agar anda dapat memantau beberapa fitur yang berkaitan...";
        mail($to, $subject, $message, $header);
        
        $data = array(
            "message"   => "anda telah login sebagai Guest",
            "id"        => 123
        );

        return json_encode($data);
    });
    //===================================================================================

    //user Settings
    $app->post('/change/name', function(Request $request, Response $response){
        $name = $request->getParam('name');
        $id = $request->getParam('id');

        $db = new db();
        $db = $db->connect();

        try{
            $sql = "UPDATE user SET name = :name WHERE id = :id";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(":name", $name);
            $stmt->bindParam(":id", $id);
            $stmt->execute();

            $message = "berhasil mengubah nama";
            $data = array(
                "message"   => $message,
                "name"      => $name
            );
            return json_encode($data);

        } catch (PDOException $e){
            return json_encode(array("message" => $e->getMessage()));
        }
    });

    $app->post('/change/pw', function(Request $request, Response $response){
        $id = intval($request->getParam('id'));
        $password = $request->getParam('password');

        try{
            $db = new db();
            $db = $db->connect();

            $password = md5($password);
            $sql = "UPDATE user SET password = :password WHERE id = :id";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(":id", $id);
            $stmt->bindParam(":password", $password);
            $stmt->execute();

            return json_encode(array("message" => "berhasil mengubah password"));

        } catch (PDOException $e){
            return json_encode(array("message" => $e->getMessage()));
        }
    });

    $app->post('/credit/add', function(Request $request, Response $response){
        
        $id = intval($request->getParam('id'));
        $credit = intval($request->getParam('credit'));
        
        try {

            $db = new db();
            $db = $db->connect();

            $sql = "UPDATE user SET credit = :credit WHERE id = :id";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(":credit", $credit);
            $stmt->bindParam(":id", $id);
            $stmt->execute();

            $sql = "SELECT email FROM user WHERE id = :id";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(":id", $id);
            $stmt->execute();
            $email = $stmt->fetch(PDO::FETCH_ASSOC)['email'];

            $generate = "rtrain-" . crc32(date("20y-m-d") . $email);
            $from = "rbttbr222@gmail.com";
            $header = "From: " . $from;
            $to = $email;
            $subject = "isi saldo ticket R-Train";
            $message = "kode pembayaran anda adalah " . $generate;
            $message .= "<br>segera lakukan pembayaran secepatnya.";
            $message .= "<br>Terima kasih";
            mail($to, $subject, $message, $header);

            return json_encode(array("info" => true));

        } catch (PDOException $e){
            return $e->getMessage();
        }
        
    });
    //===================================================================================

    //manage User
    $app->get('/user/show', function(Request $request, Response $response){
        try{
            $db = new db();
            $db = $db->connect();

            $sql = "SELECT * FROM user ORDER BY id ASC";
            $stmt = $db->prepare($sql);
            $stmt->execute();

            while($fetch = $stmt->fetch(PDO::FETCH_ASSOC)){
                $data[] = array(
                    "id"        => $fetch['id'],
                    "name"      => $fetch['name'],
                    "username"  => $fetch['username'],
                    "email"     => $fetch['email'],
                    "credit"    => $fetch['credit']
                );
            }

            return json_encode(array("user" => $data));
        } catch (PDOException $e){
            $e->getMessage();
        }
    });

    $app->post('/user/update', function(Request $request, Response $response){
		
        $id = intval($request->getParam('id'));
        $name = $request->getParam('name');
        $username = $request->getParam('username');
        $email = $request->getParam('email');
        $credit = intval($request->getParam('credit'));
		
        try{

            $db = new db();
            $db = $db->connect();

            $sql = "SELECT * FROM user WHERE name = :name AND username = :username AND email = :email";
			$stmt = $db->prepare($sql);
			$stmt->bindParam(":name", $name);
			$stmt->bindParam(":username", $username);
			$stmt->bindParam(":email", $email);
			$stmt->execute();
			
			if($stmt->rowCount() > 0 && $stmt->fetch(PDO::FETCH_ASSOC)['id'] != $id){
				$data = array(
					"message"	=> "user dengan deskripsi tersebut telah terdaftar",
					"info"		=> false
				);
			} else {
				
				$sql = "UPDATE user SET name = :name, username = :username, email = :email, credit = :credit WHERE id = :id";
				$stmt = $db->prepare($sql);
				$stmt->bindParam(":id", $id);
				$stmt->bindParam(":name", $name);
				$stmt->bindParam(":username", $username);
				$stmt->bindParam(":email", $email);
				$stmt->bindParam(":credit", $credit);
				$stmt->execute();
				
				$data = array(
					"message"	=> "berhasil update data",
					"info"		=> true
				);
				
			}

            return json_encode($data);

        } catch (PDOException $e){
            return $e->getMessage();
        }
    });

    $app->post('/user/delete', function(Request $request, Response $response){
        $id = $request->getParam('id');
        try{
            $db = new db();
            $db = $db->connect();

            $sql = "DELETE FROM user WHERE id = :id";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(":id", $id);
            $stmt->execute();
            
            return json_encode(array("message" => "berhasil hapus user"));
        } catch (PDOException $e) {
            return $e->getMessage();
        }
    });

    $app->post('/user/add', function(Request $request, Response $response){
        $name = $request->getParam('name');
        $username = $request->getParam('username');
        $password = $request->getParam('password');
        $email = $request->getParam('email');
        $credit = $request->getParam('credit');
        try{

            $db = new db();
            $db = $db->connect();

            $sql = "SELECT * FROM user WHERE username = :username";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(":username", $username);
            $stmt->execute();
            if($stmt->rowCount() > 0){
                $data = array(
                    "message"   => "username tersebut telah terdaftar",
                    "info"      => false
                );
            } else {
                try {

                    $sql = "SELECT * FROM user WHERE email = :email";
                    $stmt = $db->prepare($sql);
                    $stmt->bindParam(":email", $email);
                    $stmt->execute();
                    if($stmt->rowCount() > 0){
                        $data = array(
                            "message"   => "email tersebut telah terdaftar",
                            "info"      => false
                        );
                    } else {

                        $sql = "INSERT INTO user(name, username, password, email, credit)
                                VALUES(:name, :username, :password, :email, :credit)";
                        $stmt = $db->prepare($sql);
                        
                        $stmt->bindParam(":name", $name);
                        $stmt->bindParam(":username", $username);
                        $stmt->bindParam(":password", $password);
                        $stmt->bindParam(":email", $email);
                        $stmt->bindParam(":credit", $credit);
                        $stmt->execute();

                        $data = array(
                            "message"   => "berhasil tambah data",
                            "info"      =>true
                        );
                    }

                } catch (PDOException $t){
                    return $t->getMessage();
                }
            }

            return json_encode($data);

        } catch (PDOException $e){
            return $e->getMessage();
        }
    });
    //=================================================================================

    //manage Admin
    $app->get('/admin/show', function(Request $request, Response $response){
        try{
            $db = new db();
            $db = $db->connect();

            $sql = "SELECT * FROM admin ORDER BY id ASC";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            while($fetch = $stmt->fetch(PDO::FETCH_ASSOC)){
                if($fetch['name'] != "root"){
                    $data[] = array(
                        "id"        => $fetch['id'],
                        "name"      => $fetch['name'],
                        "username"  => $fetch['username'],
                        "email"     => $fetch['email']
                    );
                }
            }

            return json_encode(array("admin" => $data));

        } catch (PDOException $e){
            return $e->getMessage();
        }
    });

    $app->post('/admin/update', function(Request $request, Response $response){
        $id = $request->getParam('id');
        $name = $request->getParam('name');
        $username = $request->getParam('username');
        $email = $request->getParam('email');
        
        try{

            $db = new db();
            $db = $db->connect();

            $sql = "SELECT * FROM admin WHERE name = :name AND username = :username AND email = :email";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(":name", $name);
			$stmt->bindParam(":username", $username);
			$stmt->bindParam(":email", $email);
            $stmt->execute();
            
			if($stmt->rowCount() > 0 && $stmt->fetch(PDO::FETCH_ASSOC)['id'] != $id){
				$data = array(
					"message"	=> "admin dengan deskripsi tersebut telah terdaftar",
					"info"		=> false
				);
			} else {
				
				$sql = "UPDATE admin SET name = :name, username = :username, email = :email WHERE id = :id";
				$stmt = $db->prepare($sql);
				$stmt->bindParam(":id", $id);
				$stmt->bindParam(":name", $name);
				$stmt->bindParam(":username", $username);
				$stmt->bindParam(":email", $email);
				$stmt->execute();
				
				$data = array(
					"message"	=> "berhasil update data",
					"info"		=> true
				);
				
			}
			
            return json_encode($data);

        } catch (PDOException $e){
            return $e->getMessage();
        }
    });

    $app->post('/admin/delete', function(Request $request, Response $response){
        $id = $request->getParam('id');
        
        try{
            $db = new db();
            $db = $db->connect();

            $sql = "DELETE FROM admin WHERE id = :id";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(":id", $id);
            $stmt->execute();

            return json_encode(array("message" => "berhasil hapus admin"));
        } catch (PDOException $e){
            return $e->getMessage();
        }
    });
    //========================================================================================

    //admin Settings
    $app->post('/admin/change/password', function(Request $request, Response $response){
        $id = $request->getParam('id');
        $password = $request->getParam('password');
        $password = md5($password);

        try {

            $db = new db();
            $db = $db->connect();

            $sql = "UPDATE admin SET password = :password WHERE id = :id";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(":password", $password);
            $stmt->bindParam(":id", $id);
            $stmt->execute();

            $data = array(
                "message"   => "berhasil update data",
                "info"      => true
            );

            return json_encode($data);

        } catch (PDOException $e){
            return $e->getMessage();
        }
    });

    $app->post('/admin/change/username', function(Request $request, Response $response){
        $id = $request->getParam('id');
        $username = $request->getParam('username');

        try{

            $db = new db();
            $db = $db->connect();

            $sql = "SELECT * FROM admin WHERE username = :username";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(":username", $username);
            $stmt->execute();

            if($stmt->rowCount() > 0 && $stmt->fetch(PDO::FETCH_ASSOC)['id'] != $id){
                $data = array(
                    "message"   => "username tersebut telah terdaftar",
                    "info"      => false
                );
            } else {

                $sql = "UPDATE admin SET username = :username WHERE id = :id";
                $stmt->bindParam(":id", $id);
                $stmt->bindParam(":username", $username);
                $stmt->execute();
                
                $data = array(
                    "message"   => "berhasil update data",
                    "info"      => true
                );

            }

            return json_encode($data);

        } catch (PDOException $e) {
            return $e->getMessage();
        }
    });

    $app->post('/admin/change/email', function(Request $request, Response $response){
        $id = $request->getParam('id');
        $email = $request->getParam('email');
        
        try {

            $db = new db();
            $db = $db->connect();

            $sql = "SELECT * FROM admin WHERE email = :email";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(":email", $email);
            $stmt->execute();

            if($stmt->rowCount() > 0 && $stmt->fetch(PDO::FETCH_ASSOC)['id'] != $id){
                $data = array(
                    "message"   => "email tersebut telah terdaftar",
                    "info"      =>false
                );
            } else {

                $sql = "UPDATE admin SET email = :email WHERE id = :id";
                $stmt = $db->prepare($sql);
                $stmt->bindParam(":id", $id);
                $stmt->bindParam(":email", $email);
                $stmt->execute();

                $data = array(
                    "message"   => "berhasil update data",
                    "info"      => true
                );

            }

            return json_encode($data);

        } catch (PDOException $e) {
            return $e->getMessage();
        }
    });

    $app->post('/admin/change/name', function(Request $request, Response $response){
        $id = $request->getParam('id');
        $name = $request->getParam('name');

        try {

            $db = new db();
            $db = $db->connect();

            $sql = "SELECT * FROM admin WHERE name = :name";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(":name", $name);
            $stmt->execute();

            if($stmt->rowCount() > 0 && $stmt->fetch(PDO::FETCH_ASSOC)['id'] != $id){
                $data = array(
                    "message"   => "nama tersebut telah terdaftar",
                    "info"      => false
                );
            } else {

                $sql = "UPDATE admin SET name = :name WHERE id = :id";
                $stmt = $db->prepare($sql);
                $stmt->bindParam(":id", $id);
                $stmt->bindParam(":name", $name);
                $stmt->execute();

                $data = array(
                    "message"   => "berhasil update data",
                    "info"      => true
                );

            }

            return json_encode($data);

        } catch (PDOException $e){
            return $e->getMessage();
        }
    });
    //=======================================================================================

    //manage Item AND list Item
    $app->get('/item/show', function(Request $request, Response $response){
        try {
            $db = new db();
            $db = $db->connect();

            $sql = "SELECT * FROM item";
            $stmt = $db->prepare($sql);
            $stmt->execute();

            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return json_encode(array("item" => $data));

        } catch (PDOException $e){
            return $e->getMessage();
        }
    });

    $app->post('/item/add', function(Request $request, Response $response){
        $name = $request->getParam('name');
        $price = $request->getParam('price');
        $desc = $request->getParam('desc');

        $dir = __DIR__ . "/pic/";
        @$server = $_SERVER['HTTP_HOST'];
        $path = "https://$server/rtrain/src/pic/";
        $file = $_FILES['photo']['name'];
        $tmp = $_FILES['photo']['tmp_name'];

        try {

            $db = new db();
            $db = $db->connect();

            $sql = "SELECT * FROM item WHERE name = :name AND price = :price AND description = :desc";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(":name", $name);
            $stmt->bindParam(":price", $price);
            $stmt->bindParam(":desc", $desc);
            $stmt->execute();

            if($stmt->rowCount() > 0){
                $data = array(
                    "message"   => "item tersebut telah terdaftar",
                    "info"      => false
                );
            } else {

                $sql = "INSERT INTO item(name, price, description) VALUES(:name, :price, :desc)";
                $stmt = $db->prepare($sql);
                $stmt->bindParam(":name", $name);
                $stmt->bindParam(":price", $price);
                $stmt->bindParam(":desc", $desc);
                $stmt->execute(); 

                $sql = "SELECT * FROM item WHERE name = :name AND price = :price AND description = :desc";
                $stmt = $db->prepare($sql);
                $stmt->bindParam(":name", $name);
                $stmt->bindParam(":price", $price);
                $stmt->bindParam(":desc", $desc);
                $stmt->execute();

                $id = $stmt->fetch(PDO::FETCH_ASSOC)['id'];
                @$ext = end(explode('.', $file));
                $file = "item" . $id . "." . $ext;
                $path .= $file;
                

                $sql = "UPDATE item SET pic = :path WHERE id = :id";
                $stmt = $db->prepare($sql);
                $stmt->bindParam(":path", $path);
                $stmt->bindParam(":id",$id);
                $stmt->execute();

                move_uploaded_file($tmp, $dir.$file);

                $data = array(
                    "message"   => "berhasil tambah item",
                    "info"      => true
                );

            }

            return json_encode($data);

        } catch (PDOException $e){
            return $e->getMessage();
        }
    });

    $app->post('/item/delete', function(Request $request, Response $response){
        $id = $request->getParam('id');

        try {

            $db = new db();
            $db = $db->connect();

            $sql = "SELECT * FROM item WHERE id = :id";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(":id", $id);
            $stmt->execute();

            @$pic = $stmt->fetch(PDO::FETCH_ASSOC)['pic'];
            @$pic = end(explode('/',$pic));
            $path = "../src/pic/$pic";
            unlink($path);

            $sql = "DELETE FROM item WHERE id = :id";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(":id", $id);
            $stmt->execute();

            return json_encode(array("message" => "berhasil hapus item"));

        } catch (PDOException $e){
            return $e->getMessage();
        }
    });

    $app->post('/item/update', function(Request $request, Response $response){
        $id = $request->getParam('id');
        $name = $request->getParam('name');
        $price = $request->getParam('price');
        $desc = $request->getParam('desc');

        $dir = __DIR__ . "/pic/";
        @$server = $_SERVER['HTTP_HOST'];
        $path = "https://$server/rtrain/src/pic";
        $tmp = $_FILES['photo']['tmp_name'];
        $file = $_FILES['photo']['name'];

        @$ext = end(explode('.', $file));
        $file = "item" . $id . "." . $ext;
        $path .= $file;

        try {

            $db = new db();
            $db = $db->connect();

            $sql = "SELECT * FROM item WHERE name = :name AND price = :price AND description = :desc";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(":name", $name);
            $stmt->bindParam(":price", $price);
            $stmt->bindParam(":desc", $desc);
            $stmt->execute();

            if($stmt->rowCount() > 0 && $stmt->fetch(PDO::FETCH_ASSOC)['id'] != $id){
                $data = array(
                    "message"   => "item tersebut telah terdaftar",
                    "info"      => false
                );
            } else {
                
                $sql = "UPDATE item SET name = :name, price = :price, description = :desc WHERE id = :id";
                $stmt = $db->prepare($sql);
                $stmt->bindParam(":name", $name);
                $stmt->bindParam(":price", $price);
                $stmt->bindParam(":desc", $desc);
                $stmt->bindParam("id", $id);
                $stmt->execute();
                echo $tmp;
                move_uploaded_file($tmp, $dir.$file);

                $data = array(
                    "message"   => "berhasil update item",
                    "info"      => true
                );

            }

            return json_encode($data);

        } catch (PDOException $e){
            return $e->getMessage();
        }
    });
    //===============================================================================
    
    //manage Train AND train show
    $app->get('/train/show', function(Request $request, Response $response){

        try {

            $date = date("20y-m-d");
            $db = new db();
            $db = $db->connect();

            $sql = "SELECT t.id, t.name, t.price, t.seat, c.name AS category
                FROM Train t, category c
                WHERE c.id = t.category";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(":select", $select);
            $stmt->bindParam(":from", $from);
            $stmt->bindParam(":where", $where);
            $stmt->execute();
            $fetch = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach($fetch as $val){

                $sql = "SELECT * FROM Ticket WHERE date = :date AND TrainId = :TrainId";
                $stmt = $db->prepare($sql);
                $stmt->bindParam(":date", $date);
                $stmt->bindParam(":TrainId", $val['id']);
                $stmt->execute();
                $booked = array(
                    "booked"    => $stmt->rowCount(),
                    "date"      => $date
                );

                $train[] = array_merge($val, $booked);

            }
            
            return json_encode(array("train" => $train));

        } catch (PDOException $e){
            return $e->getMessage();
        }

    });

    //train search using date and category
    $app->post('/train/search', function(Request $request, Response $response){

        @$category = $request->getParam('category');
        $date = $request->getParam('date');
        if(!isset($date)){
            $date = date("20y-m-d");
        }

        try {
            
            $db = new db();
            $db = $db->connect();

            $sql = "SELECT t.id, t.name, t.price, t.seat, c.name AS category
                FROM Train t, category c
                WHERE c.id = t.category";

            if($category != "none"){
                $sql .= " AND c.name = :category";
                $stmt = $db->prepare($sql);
                $stmt->bindParam(":category", $category);
            } else{
                $stmt = $db->prepare($sql);
            }

            $stmt->execute();
            $fetch = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach($fetch as $val){

                $sql = "SELECT * FROM Ticket WHERE date = :date AND TrainId = :TrainId";
                $stmt = $db->prepare($sql);
                $stmt->bindParam(":date", $date);
                $stmt->bindParam(":TrainId", $val['id']);
                $stmt->execute();
                $booked = array(
                    "booked"    => $stmt->rowCount(),
                    "date"      => $date
                );

                $train[] = array_merge($val, $booked);

            }
            
            return json_encode(array("train" => $train));
            

        } catch (PDOException $e){
            return $e->getMessage();
        }

    });

    $app->post('/train/add', function(Request $request, Response $response){
        
        $name = $request->getParam('name');
        $category = $request->getParam('category');
        $price = $request->getParam('price');
        $cars = intval($request->getParam('cars'));
        $seat = $cars * 20;

        try {

            $db = new db();
            $db = $db->connect();

            $sql = "SELECT * FROM category WHERE name = :category";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(":category", $category);
            $stmt->execute();
            $categoryId = $stmt->fetch(PDO::FETCH_ASSOC)['id'];

            $sql = "SELECT * FROM Train WHERE name = :name AND category = :category AND price = :price";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(":name", $name);
            $stmt->bindParam(":category", $categoryId);
            $stmt->bindParam(":price", $price);
            $stmt->execute();

            if($stmt->rowCount() > 0){
                $data = array(
                    "message"   => "kereta dengan deskripsi tersebut telah terdaftar",
                    "info"      => false
                );
            } else {

                $sql = "INSERT INTO Train (name, category, price, seat) VALUES (:name, :category, :price, :seat)";
                $stmt = $db->prepare($sql);
                $stmt->bindParam(":name", $name);
                $stmt->bindParam(":category", $categoryId);
                $stmt->bindParam(":price", $price);
                $stmt->bindParam(":seat", $seat);
                $stmt->execute();

                $data = array(
                    "message"   => "berhasil tambah data",
                    "info"      => true
                );

            }

            return json_encode($data);

        } catch (PDOException $e){
            return $e->getMessage();
        }

    });

    $app->post('/train/delete', function(Request $request, Response $response){
        $id = $request->getParam('id');

        try{

            $db = new db();
            $db = $db->connect();

            $sql = "DELETE FROM Train WHERE id = :id";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(":id", $id);
            $stmt->execute();

            return json_encode(array("message"   => "berhasil menghapus data"));

        } catch (PDOException $e){
            return $e->getMessage();
        }
    });

    $app->post('/train/update', function(Request $request, Response $response){
        $id = $request->getParam('id');
        $name = $request->getParam('name');
        $category = $request->getParam('category');
        $cars = intval($request->getParam('cars'));
        $price = $request->getParam('price');
        $seat = intval($cars) * 20;

        try {

            $db = new db();
            $db = $db->connect();

            $sql = "SELECT * FROM category WHERE name = :category";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(":category", $category);
            $stmt->execute();
            $categoryId = $stmt->fetch(PDO::FETCH_ASSOC)['id'];

            $sql = "SELECT * FROM Train WHERE name = :name AND category = :category AND price = :price";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(":name", $name);
            $stmt->bindParam(":category", $categoryId);
            $stmt->bindParam(":price", $price);
            $stmt->execute();

            if($stmt->rowCount() > 0 && $stmt->fetch(PDO::FETCH_ASSOC)['id'] != $id){
                $data = array(
                    "message"   => "kereta dengan deskripsi tersebut telah terdaftar",
                    "info"      => false
                );
            } else {

                $sql = "UPDATE Train SET name = :name, category = :category, price = :price, seat = :seat WHERE id = :id";
                $stmt = $db->prepare($sql);
                $stmt->bindParam(":name", $name);
                $stmt->bindParam(":category", $categoryId);
                $stmt->bindParam(":price", $price);
                $stmt->bindParam(":seat", $seat);
                $stmt->bindParam(":id", $id);
                $stmt->execute();

                $data = array(
                    "message"   => "berhasil update data",
                    "info"      => true
                );

            }

            return json_encode($data);

        } catch (PDOException $e){
            return $e->getMessage();
        }
    });
    //================================================================================

    //list city to destination and departure
    $app->get('/city/show', function(Request $request, Response $response){

        try {

            $db = new db();
            $db = $db->connect();

            $sql = "SELECT name FROM city";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $city = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return json_encode(array("city" => $city));

        } catch (PDOException $e) {
            return $e->getMessage();
        }

    });

    //list seat in train
    $app->post('/seat/show', function(Request $request, Response $response){
        
        $trainId = $request->getParam('trainId');
        $date = $request->getParam('date');
        $time = $request->getParam('time');
        $category = $request->getParam('category');
        $destination = $request->getParam('destination');
        $depart = $request->getParam('depart');
        $cart = $request->getParam('cart');

        try {

            $db = new db();
            $db = $db->connect();

            $sql = "SELECT * FROM city WHERE name = :destination";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(":destination", $destination);
            $stmt->execute();
            $destId = $stmt->fetch(PDO::FETCH_ASSOC)['id'];

            $sql = "SELECT * FROM city WHERE name = :depart";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(":depart", $depart);
            $stmt->execute();
            $depId = $stmt->fetch(PDO::FETCH_ASSOC)['id'];

            $sql = "SELECT id FROM time WHERE time = :time";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(":time", $time);
            $stmt->execute();
            $timeId = $stmt->fetch(PDO::FETCH_ASSOC)['id'];

            $cart = explode("Gerbong ", $cart);
            $cart = intval(end($cart));
            $seat = ($cart - 1) * 20;
            $cart *= 20;

            for($i = $seat + 1; $i <= $cart; $i++){

                $sql = "SELECT * FROM Ticket 
                    WHERE time = :timeId AND date = :date AND seat = :seat AND destination = :destId AND depart = :depId AND trainId = :trainId";
                $stmt = $db->prepare($sql);
                $stmt->bindParam("timeId", $timeId);
                $stmt->bindParam(":date", $date);
                $stmt->bindParam(":seat", $i);
                $stmt->bindParam(":destId", $destId);
                $stmt->bindParam(":depId", $depId);
                $stmt->bindParam("trainId", $trainId);
                $stmt->execute();
                $row = $stmt->rowCount();

                if($row == 1){
                    $data[] = array(
                        "seatNum"   => $i,
                        "status"    => false
                    );
                } else {
                    $data[] = array(
                        "seatNum"   => $i,
                        "status"    => true
                    );
                }

            }

            return json_encode(array("seat" => $data));

        } catch (PDOException $e) {
            return $e->getMessage();
        }

    });

    $app->get('/time/show', function(Request $request, Response $response){

        try {

            $db = new db();
            $db = $db->connect();

            $sql = "SELECT * FROM time";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $time = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return json_encode(array("time" => $time));

        } catch (PDOException $e){
            return $e->getMessage();
        }

    });

    $app->post('/cart/show', function(Request $request, Response $response){

        $id = $request->getParam('id');

        try {

            $db = new db();
            $db = $db->connect();

            $sql = "SELECT seat FROM Train WHERE id = :id";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(":id", $id);
            $stmt->execute();

            $seat = $stmt->fetch(PDO::FETCH_ASSOC)['seat'];
            $cart = $seat / 20;

            for($i = 0; $i < $cart; $i++){

                $data[] = array(
                    "num" => ($i + 1)
                );

            }

            return json_encode(array("cart" => $data));

        } catch (PDOException $e){
            return $e->getMessage();
        }

    });

    //read status train (total booked && [ full || not full ] )
    $app->post('/train/status',function(Request $request, Response $response){

        $trainId = $request->getParam('trainId');
        $date = $request->getParam('date');
        $time = $request->getParam('time');
        $category = $request->getParam('category');
        $destination = $request->getParam('destination');
        $depart = $request->getParam('depart');
        $cart = $request->getParam('cart');
        $cart = explode("Gerbong ", $cart);
        $cart = intval(end($cart));
        $row = 0;

        try {

            $db = new db();
            $db = $db->connect();

            $sql = "SELECT id FROM category WHERE name = :category";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(":category", $category);
            $stmt->execute();
            $category = $stmt->fetch(PDO::FETCH_ASSOC)['id'];

            $sql = "SELECT id FROM time WHERE time = :time";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(":time", $time);
            $stmt->execute();
            $time = $stmt->fetch(PDO::FETCH_ASSOC)['id'];

            $sql = "SELECT id FROM city WHERE name = :depart";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(":depart", $depart);
            $stmt->execute();
            $depart = $stmt->fetch(PDO::FETCH_ASSOC)['id'];
            
            $sql = "SELECT id FROM city WHERE name = :destination";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(":destination", $destination);
            $stmt->execute();
            $destination = $stmt->fetch(PDO::FETCH_ASSOC)['id'];
            
            $seat = ($cart - 1) * 20;
            $cart *= 20;

            for($i = $seat; $i < $cart; $i++){
                $sql = "SELECT * FROM Ticket WHERE TrainId = :trainId AND date = :date AND seat = :seat
                        AND destination = :destination AND depart = :depart AND time = :time";
                $stmt = $db->prepare($sql);
                $stmt->bindParam(":trainId", $trainId);
                $stmt->bindParam("date", $date);
                $stmt->bindParam(":seat", $i);
                $stmt->bindParam(":destination", $destination);
                $stmt->bindParam(":depart", $depart);
                $stmt->bindParam(":time", $time);
                $stmt->execute();
                $row += $stmt->rowCount();
            }

            $sql = "SELECT seat FROM Train WHERE id = :trainId AND category = :category";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(":trainId", $trainId);
            $stmt->bindParam(":category", $category);
            $stmt->execute();
            $seat = $stmt->fetch(PDO::FETCH_ASSOC)['seat'];

            $row = intval($row);
            $seat = intval($seat);

            if($seat - $row == 0){
                $status = false;
            } else {
                $status = true;
            }

            return json_encode(array("info" => $status));

        } catch (PDOException $e){
            return $e->getMessage();
        }

    });//add cart

    $app->post('/ticket/purchase', function(Request $request, Response $response){
        $trainId = $request->getParam("trainId");
        $userId = $request->getParam("userId");
        $date = $request->getParam("date");
        $seat = $request->getParam("seat");
        $destination = $request->getParam("destination");
        $depart = $request->getParam("depart");
        $time = $request->getParam("time");
        $price = intval($request->getParam("price"));
        $credit = intval($request->getParam("credit"));
        $cart = $request->getParam("cart");
        $type = $request->getParam("type");
        $ktp = $request->getParam("ktp");
        $email = $request->getParam('email');
        $rolls = 1;

        try {

            $db = new db();
            $db = $db->connect();

            $sql = "SELECT id FROM city WHERE name = :destination";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(":destination", $destination);
            $stmt->execute();
            $destination = $stmt->fetch(PDO::FETCH_ASSOC)['id'];

            $sql = "SELECT id FROM city WHERE name = :depart";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(":depart", $depart);
            $stmt->execute();
            $depart = $stmt->fetch(PDO::FETCH_ASSOC)['id'];

            $sql = "SELECT id FROM time WHERE time = :time";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(":time", $time);
            $stmt->execute();
            $time = $stmt->fetch(PDO::FETCH_ASSOC)['id'];

            $seat = explode(",", $seat);
            $cart = explode("Gerbong ", $cart);
            $cart = intval(end($cart));
            $ktp = explode(",", $ktp);

            foreach($seat as $key => $val){

                $val = intval($val);
                $val = (($cart - 1) * 20) + $val;

                $sql = "INSERT INTO Ticket (TrainId, UserId, date, seat, destination, depart, time, ktp)
                        VALUES (:trainId, :userId, :date, :seat, :destination, :depart, :time, :ktp)";
                $stmt = $db->prepare($sql);
                $stmt->bindParam(":trainId", $trainId);
                $stmt->bindParam(":userId", $userId);
                $stmt->bindParam(":date", $date);
                $stmt->bindParam(":seat", $val);
                $stmt->bindParam(":destination", $destination);
                $stmt->bindParam(":depart", $depart);
                $stmt->bindParam(":time", $time);
                $stmt->bindParam(":ktp", $ktp[$key]);
                $stmt->execute();

                $sql = "SELECT id FROM Ticket WHERE TrainId = :trainId AND date = :date AND seat = :seat AND ktp = :ktp
                    AND destination = :destination AND depart = :depart AND time = :time AND UserId = :userId";
                $stmt = $db->prepare($sql);
                $stmt->bindParam(":trainId", $trainId);
                $stmt->bindParam(":userId", $userId);
                $stmt->bindParam(":date", $date);
                $stmt->bindParam(":seat", $val);
                $stmt->bindParam(":ktp", $ktp[$key]);
                $stmt->bindParam(":destination", $destination);
                $stmt->bindParam(":depart", $depart);
                $stmt->bindParam(":time", $time);
                $stmt->execute();
                $purchaseId = $stmt->fetch(PDO::FETCH_ASSOC)['id'];

                $sql = "INSERT INTO history(UserId, rolls, purchaseId)
                    VALUES(:userId, :rolls, :purchaseId)";
                $stmt = $db->prepare($sql);
                $stmt->bindParam(":userId", $userId);
                $stmt->bindParam(":rolls", $rolls);
                $stmt->bindParam(":purchaseId", $purchaseId);
                $stmt->execute();

            }

            if($type == "user"){
                $credit -= $price;
                $sql = "UPDATE user SET credit = :credit WHERE id = :id";
                $stmt = $db->prepare($sql);
                $stmt->bindParam(":credit", $credit);
                $stmt->bindParam(":id", $userId);
                $stmt->execute();
                $message = "berhasil membeli ticket";
            } else if($type == "guest"){

                $generate = "rtrain-" . crc32(date("20y-m-d") . $email);
                $from = "rbttbr222@gmail.com";
                $header = "From: " . $from;
                $to = $email;
                $subject = "Pembelian ticket R-Train";
                $message = "kode pembayaran anda adalah " . $generate;
                $message .= "<br>segera lakukan pembayaran secepatnya.";
                $message .= "<br>Terima kasih";
                mail($to, $subject, $message, $header);

                $message = "detail pembayaran akan dikirim melalui email";
            }

            $data = array(
                "message"   => $message,
                "info"      => true,
                "credit"    => $credit
            );

            return json_encode($data);

        } catch (PDOException $e){
            return $e->getMessage();
        }
    });
    //===================================================================================

    //show history
    $app->post('/history/show', function(Request $request, Response $response){

        $id = $request->getParam('id');

        try {

            $db = new db();
            $db = $db->connect();

            $sql = "SELECT h.id, h.purchaseId, h.rolls, r.name AS type
                FROM history h, rolls r
                WHERE h.UserId = :id AND r.id = h.rolls";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(":id", $id);
            $stmt->execute();
            $fetch = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $row = $stmt->rowCount();

            if($row > 0){
                foreach($fetch AS $val){

                    if($val['rolls'] == 1){
                        $sql = "SELECT date FROM Ticket WHERE id = :purchaseId";
                    } else if ($val['rolls'] == 2){
                        $sql = "SELECT date FROM PurchaseItems WHERE id = :purchaseId";
                    }

                    $stmt = $db->prepare($sql);
                    $stmt->bindParam(":purchaseId", $val['purchaseId']);
                    $stmt->execute();
                    $date = $stmt->fetch(PDO::FETCH_ASSOC)['date'];

                    $data[] = array(
                        "id"            => $val['id'],
                        "purchaseId"    => $val['purchaseId'],
                        "type"          => $val['type'],
                        "date"          => $date
                    );
                }
                $info = true;
            } else { 
                $data[] = array(
                    "id"            => "1",
                    "purchasId"     => "1",
                    "type"          => "1",
                    "date"          => "1"
                );
                $info = false;
            }

            $data = array(
                "history"   => $data,
                "info"      => $info
            );

            return json_encode($data);

        } catch (PDOException $e){
            return $e->getMessage();
        }

    });

    $app->post('/history/delete', function(Request $request, Response $response){
        
        $id = $request->getParam('id');
        
        try {

            $db = new db();
            $db = $db->connect();

            $sql = "DELETE FROM history WHERE id = :id";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(":id", $id);
            $stmt->execute();
            $data = array("info" => true);

            return json_encode($data);
            
        } catch (PDOException $e){
            return $e->getMessage();
        }
    });

    $app->post('/history/delete/all',function(Request $request, Response $response){
        
        $userId = $request->getParam('id');

        try {

            $db = new db();
            $db = $db->connect();

            $sql = "DELETE FROM history WHERE UserId = :userId";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(":userId", $userId);
            $stmt->execute();
            
            $data = array("info" => true);

            return json_encode($data);

        } catch (PDOException $e){
            return $e->getMessage();
        }
    });

    $app->get('/system/history/delete', function(Request $request, Response $response){

        $day = date("d");
        $month = date("m");
        $year = date("20y");
        $now = $year . $month . $day;
        $now = intval($now) - 2;

        try {

            $db = new db();
            $db = $db->connect();

            $sql = "SELECT * FROM Ticket";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $row = $stmt->rowCount();
            $fetch = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if($row > 0){
                foreach($fetch as $val){
                    $date = explode("-", $val['date']);
                    $date = implode("", $date);
                    $date = intval($date);
                    if($date <= $now){
                        $sql = "DELETE FROM Ticket WHERE id = :id";
                        $stmt = $db->prepare($sql);
                        $stmt->bindParam(":id", $val['id']);
                    }
                }
            }

            $sql = "SELECT * FROM PurchaseItems";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $row = $stmt->rowCount();
            $fetch = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if($row > 0){
                foreach($fetch as $val){
                    $date = explode("-", $val['date']);
                    $date = implode("", $date);
                    $date = intval($date);
                    if($date <= $now){
                        $sql = "DELETE FROM PurchaseItems WHERE id = :id";
                        $stmt = $db->prepare($sql);
                        $stmt->bindParam(":id", $val['id']);
                        $stmt->execute();
                    }
                }
            }

            $rolls = 1;
            $sql = "SELECT * FROM history WHERE rolls = :rolls";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(":rolls", $rolls);
            $stmt->execute();
            $row = $stmt->rowCount();
            $fetch = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if($row > 0){
                foreach($fetch as $val){
                    $sql = "SELECT * FROM Ticket WHERE id = :id";
                    $stmt = $db->prepare($sql);
                    $stmt->bindParam(":id", $val['purchaseId']);
                    $stmt->execute();
                    if($stmt->rowCount() == 0){
                        $sql = "DELETE FROM history WHERE id = :id";
                        $stmt = $db->prepare($sql);
                        $stmt->bindParam(":id", $val['id']);
                        $stmt->execute();
                    }
                }
            }

            $rolls = 2;
            $sql = "SELECT * FROM history WHERE rolls = :rolls";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(":rolls", $rolls);
            $stmt->execute();
            $row = $stmt->rowCount();
            $fetch = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if($row > 0){
                foreach($fetch as $val){
                    $sql = "SELECT * FROM PurchaseItems WHERE id = :id";
                    $stmt = $db->prepare($sql);
                    $stmt->bindParam(":id", $val['purchaseId']);
                    $stmt->execute();
                    if($stmt->rowCount() == 0){
                        $sql = "DELETE FROM history WHERE id = :id";
                        $stmt = $db->prepare($sql);
                        $stmt->bindParam(":id", $val['id']);
                        $stmt->execute();
                    }
                }
            }

            return json_encode(array("info" => true));

        } catch (PDOException $e){
            return $e->getMessage();
        }
    });

    $app->post('/history/ticket', function(Request $request, Response $response){

        $id = $request->getParam('id');

        try {

            $db = new db();
            $db = $db->connect();

            $sql = "SELECT * FROM Ticket WHERE id = :id";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(":id", $id);
            $stmt->execute();
            $val = $stmt->fetch(PDO::FETCH_ASSOC);

            $trainId = $val['TrainId'];
            $date = $val['date'];
            $seat = $val['seat'];
            $destination = $val['destination'];
            $depart = $val['depart'];
            $time = $val['time'];
            $ktp = $val['ktp'];

            $sql = "SELECT name, category, price FROM Train WHERE id = :id";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(":id", $trainId);
            $stmt->execute();
            $fetch = $stmt->fetch(PDO::FETCH_ASSOC);
            $trainName = $fetch['name'];
            $category = $fetch['category'];
            $price = "Rp " . $fetch['price'];

            $sql = "SELECT name FROM category WHERE id = :id";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(":id", $category);
            $stmt->execute();
            $category = $stmt->fetch(PDO::FETCH_ASSOC)['name'];

            $sql = "SELECT name FROM city WHERE id = :id";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(":id", $destination);
            $stmt->execute();
            $destination = $stmt->fetch(PDO::FETCH_ASSOC)['name'];

            $sql = "SELECT name FROM city WHERE id = :id";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(":id", $depart);
            $stmt->execute();
            $depart = $stmt->fetch(PDO::FETCH_ASSOC)['name'];

            $sql = "SELECT time FROM time WHERE id = :id";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(":id", $time);
            $stmt->execute();
            $time = $stmt->fetch(PDO::FETCH_ASSOC)['time'];

            $data = array(
                "trainName"     => $trainName,
                "category"      => $category,
                "date"          => $date,
                "seat"          => $seat,
                "destination"   => $destination,
                "depart"        => $depart,
                "time"          => $time,
                "price"         => $price,
                "ktp"           => $ktp
            );

            return json_encode($data);

        } catch (PDOException $e){
            return $e->getMessage();
        }

    });

    $app->post('/history/item', function(Request $request, Response $response){

        $id = $request->getParam('id');

        try {

            $db = new db();
            $db = $db->connect();

            $sql = "SELECT * FROM PurchaseItems WHERE id = :id";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(":id", $id);
            $stmt->execute();
            $val = $stmt->fetch(PDO::FETCH_ASSOC);

            $itemId = $val['ItemId'];
            $qty = $val['Qty'];
            $price = $val['price'];
            $date = $val['date'];
            $address = $val['address'];

            $sql = "SELECT name, description, pic FROM item WHERE id = :id";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(":id", $itemId);
            $stmt->execute();
            $fetch = $stmt->fetch(PDO::FETCH_ASSOC);
            $itemName = $fetch['name'];
            $desc = $fetch['description'];
            $pic = $fetch['pic'];

            $data = array(
                "itemName"  => $itemName,
                "desc"      => $desc,
                "pic"       => $pic,
                "Qty"       => $qty,
                "price"     => $price,
                "date"      => $date,
                "address"   => $address
            );

            return json_encode($data);

        } catch (PDOException $e){
            return $e->getMessage();
        }

    });
    //==================================================================================

    //buy an item
    $app->post('/item/buy', function(Request $request, Response $response){

        $id = $request->getParam('id');
        $item = $request->getParam('item');
        $qty = $request->getParam('qty');
        $price = $request->getParam('price');
        $email = $request->getParam('email');
        $addr = $request->getParam('address');
        $type = $request->getParam('type');
        $date = date("20y-m-d");

        try {

            $db = new db();
            $db = $db->connect();

            $sql = "INSERT INTO PurchaseItems(UserId, ItemId, Qty, price, date, address)
                VALUES(:id, :item, :qty, :price, :date, :addr)";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(":id", $id);
            $stmt->bindParam(":item", $item);
            $stmt->bindParam(":qty", $qty);
            $stmt->bindParam(":price", $price);
            $stmt->bindParam(":date", $date);
            $stmt->bindParam(":addr", $addr);
            $stmt->execute();

            $sql = "SELECT id FROM PurchaseItems WHERE UserId = :id AND ItemId = :item AND Qty = :qty AND price = :price AND date = :date";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(":id", $id);
            $stmt->bindParam(":item", $item);
            $stmt->bindParam(":qty", $qty);
            $stmt->bindParam(":price", $price);
            $stmt->bindParam(":date", $date);
            $stmt->execute();
            $fetch = $stmt->fetch(PDO::FETCH_ASSOC);
            $purchaseId = $fetch['id'];
            $rolls = 2;

            $sql = "SELECT name, description FROM item WHERE id = :id";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(":id", $purchaseId);
            $stmt->execute();
            $fetch = $stmt->fetch(PDO::FETCH_ASSOC);
            $name = $fetch['name'];
            $desc = $fetch['description'];

            $sql = "INSERT INTO history(UserId, rolls, purchaseId)
                VALUES(:id, :rolls, :purchaseId)";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(":id", $id);
            $stmt->bindParam(":rolls", $rolls);
            $stmt->bindParam(":purchaseId", $purchaseId);
            $stmt->execute();

            if ($type == 1){

                $sql = "SELECT credit FROM user WHERE id = :id";
                $stmt = $db->prepare($sql);
                $stmt->bindParam(":id", $id);
                $stmt->execute();
                $credit = intval($stmt->fetch(PDO::FETCH_ASSOC)['credit']);
                $credit -= $price;

                $sql = "UPDATE user SET credit = :credit WHERE id = :id";
                $stmt = $db->prepare($sql);
                $stmt->bindParam(":credit", $credit);
                $stmt->bindParam(":id", $id);
                $stmt->execute();

                $message = array(
                    "anda telah berhasil melakukan pembelian item",
                    "",
                    "tunggu 2-3 hari barang akan di kirim ke " . $addr,
                    "",
                    "nama item = " . $name,
                    "jumlah    = " . $qty,
                    "harga     = " . $price,
                    "deskripsi item = <br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . $desc
                );

            } else {
                $credit = 0;
                $generate = "rtrain-" . crc32(date("20y-m-d") . $email);
                $message = array(
                    "kode pembayaran anda adalah " . $generate,
                    "segera lakukan pembayaran anda terima kasih",
                    "",
                    "tunggu 2-3 hari barang akan di kirim ke " . $addr,
                    "",
                    "nama item = " . $name,
                    "jumlah    = " . $qty,
                    "harga     = " . $price,
                    "deskripsi item = <br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . $desc
                );
            }

            $from = "rbttbr222@gmail.com";
            $header = "From: ".$from;
            $to = $email;
            $subject = "pembelian item R-Train";
            $message = implode("<br>", $message);
            mail($to, $subject, $message, $header);
            $data = array(
                "info"      => true,
                "credit"    => $credit
            );

            return json_encode($data);

        } catch (PDOException $e){
            return $e->getMessage();
        }

    });

    //manage time
    $app->post('/add/time', function(Request $request, Response $response){
        
        $time = $request->getParam('time');
        
        try{

            $db = new db();
            $db = $db->connect();

            $sql = "SELECT * FROM time WHERE time = :time";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(":time", $time);
            $stmt->execute();

            if($stmt->rowCount() > 0){
                $message = "waktu tersebut telah terdaftar";
                $info = false;
            } else {

                $sql = "INSERT INTO time(time) VALUES(:time)";
                $stmt = $db->prepare($sql);
                $stmt->bindParam(":time", $time);
                $stmt->execute();

                $message = "berhasil tambah waktu";
                $info = true;
            
            }

            $data = array(
                "message"   => $message,
                "info"      => $info
            );

            return json_encode($data);

        }catch (PDOException $e){
            return $e->getMessage();
        }
    });

    $app->post('/edit/time', function(Request $request, Response $response){
        
        $time = $request->getParam('time');
        $old = $request->getParam('old');

        try {

            $db = new db();
            $db = $db->connect();

            $sql = "SELECT * FROM time WHERE time = :time";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(":time", $time);
            $stmt->execute();
            
            if($stmt->rowCount() > 0){
                $message = "waktu tersebut telah terdaftar";
                $info = false;
            } else {

                $sql = "SELECT id FROM time WHERE time = :time";
                $stmt = $db->prepare($sql);
                $stmt->bindParam(":time", $old);
                $stmt->execute();
                $id = $stmt->fetch(PDO::FETCH_ASSOC)['id'];

                $sql = "UPDATE time SET time = :time WHERE id = :id";
                $stmt = $db->prepare($sql);
                $stmt->bindParam(":time", $time);
                $stmt->bindParam(":id", $id);
                $stmt->execute();

                $message = "Berhasil update data";
                $info = true;

            }

            $data = array(
                "message"   => $message,
                "info"      => $info
            );

            return json_encode($data);

        } catch (PDOException $e){
            return $e->getMessage();
        }

    });

    $app->post('/delete/time', function(Request $request, Response $response){

        $time = $request->getParam('time');

        try {

            $db = new db();
            $db = $db->connect();

            $sql = "DELETE FROM time WHERE time = :time";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(":time", $time);
            $stmt->execute();

            return json_encode(array("info" => true));

        } catch (PDOException $e){
            return $e->getMessage();
        }

    });

    //manage city
    $app->post('/add/city', function(Request $request, Response $response){
        
        $city = $request->getParam('city');
        
        try{

            $db = new db();
            $db = $db->connect();

            $sql = "SELECT * FROM city WHERE name = :name";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(":name", $city);
            $stmt->execute();

            if($stmt->rowCount() > 0){
                $message = "kota tersebut telah terdaftar";
                $info = false;
            } else {

                $sql = "INSERT INTO city(name) VALUES(:name)";
                $stmt = $db->prepare($sql);
                $stmt->bindParam(":name", $city);
                $stmt->execute();

                $message = "berhasil tambah kota";
                $info = true;

            }

            $data = array(
                "message"   => $message,
                "info"      => $info
            );

            return json_encode($data);

        }catch (PDOException $e){
            return $e->getMessage();
        }
    });

    $app->post('/edit/city', function(Request $request, Response $response){
        
        $city = $request->getParam('city');
        $old = $request->getParam('old');

        try {

            $db = new db();
            $db = $db->connect();

            $sql = "SELECT * FROM city WHERE name = :name";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(":name", $city);
            $stmt->execute();
            
            if($stmt->rowCount() > 0){
                $message = "kota tersebut telah terdaftar";
                $info = false;
            } else {

                $sql = "UPDATE city SET name = :name WHERE name = :old";
                $stmt = $db->prepare($sql);
                $stmt->bindParam(":name", $city);
                $stmt->bindParam(":old", $old);
                $stmt->execute();

                $message = "Berhasil update data";
                $info = true;

            }

            $data = array(
                "message"   => $message,
                "info"      => $info
            );

            return json_encode($data);

        } catch (PDOException $e){
            return $e->getMessage();
        }

    });

    $app->post('/delete/city', function(Request $request, Response $response){

        $city = $request->getParam("city");

        try {

            $db = new db();
            $db = $db->connect();

            $sql = "DELETE FROM city WHERE name = :name";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(":name", $city);
            $stmt->execute();

            return json_encode(array("info" => true));

        } catch (PDOException $e){
            return $e->getMessage();
        }

    });

    //category
    $app->get('/show/category', function(Request $request, Response $response){
        try{

            $db = new db();
            $db = $db->connect();

            $sql = "SELECT * FROM category";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return json_encode(array("category" => $data));

        }catch (PDOException $e){
            return $e->getMessage();
        }
    });

    $app->get('/manage/train', function(Request $request, Response $response){
        try{

            $db = new db();
            $db = $db->connect();

            $sql = "SELECT t.id, t.name, t.price, t.seat, c.name AS category FROM Train t, category c WHERE t.category = c.id";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $fetch = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach($fetch as $val){
                $cars = intval($val['seat']);
                $cars /= 20;
                $data[] = array(
                    "id"        => $val['id'],
                    "name"      => $val['name'],
                    "category"  => $val['category'],
                    "price"     => $val['price'],
                    "cars"      => $cars
                );
            }

            return json_encode(array("train" => $data));

        } catch (PDOException $e){
            return $e->getMessage();
        }
    });

?>