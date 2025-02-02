<?php   
    //check neu ko phai truong phong thi chuyen ve page index
	session_start();
	require_once('../../function.php');
    if (!isset($_SESSION['user'])) {
        header('Location: /login.php');
        exit();
    }else{
        $user = $_SESSION['user'];

        $sql = "SELECT * FROM account WHERE username=?";
		$conn = connect_db();
        $stm = $conn->prepare($sql);
        $stm->bind_param("s", $user); 
            
            
		if (!$stm->execute()){
			die('can not execute: ' . $stm->error);
		}
        $result = $stm->get_result();
        $data = $result->fetch_assoc();
        if(password_verify($user, $data['password']) == true){
            header('Location: ../../changepass.php');
            exit();
        }
    }
	$sql = "SELECT * FROM account where username = ?";
    $conn = connect_db();
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $_SESSION['user']);

	if (!$stmt->execute()) {
		return array(
			'code' => 2,
			'message' => 'An error occured: ' . $stmt->error,
		);
	}
	$result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $idPersonPer = $data['id'];
    $namePersonPer = $data['name'];

    $_SESSION['role'] = $data['role'];
	if ($_SESSION['role'] !== 2) {
		header("Location: ../../index.php");
		exit();
	}
?>
<?php
    $id = $_GET['id']; 
    if(isset($_POST['id']) && !empty($_POST['id'])){
        require_once('../../admin/db.php');
        if($id != 0){
            $sql = "SELECT * FROM dayOff where idDayOff = ?";
            $conn = connect_db();
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);

            if (!$stmt->execute()) {
                return array(
                    'code' => 2,
                    'message' => 'An error occured: ' . $stmt->error,
                );
            }
            $result = $stmt->get_result();
            $data = $result->fetch_assoc();
            $idPerson = $data['idPerson'];
            $dayOffWant = $data['dayOffWant'];
            $dayOffUsed = $data['dayOffWant'];
            $idStatus = 2;
            $Status = "Approved";

            $sql = "SELECT * FROM account where id = ?";
            $conn = connect_db();
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $idPerson);

            if (!$stmt->execute()) {
                return array(
                    'code' => 2,
                    'message' => 'An error occured: ' . $stmt->error,
                );
            }
            $result = $stmt->get_result();
            $data = $result->fetch_assoc();
            $dayOff = $data['dayOffLeft'];
            $dayOffUsedA = $dayOffUsed + $data['dayOffUsed'];
            $dayOffLeft = $dayOff - $dayOffUsed;

            // update bên dayOff
            $sql = "UPDATE dayOff SET dayOffUsed=?, dayOffWant=?, dayOffLeft=?, idStatus=?, Status=?, idPersonPer=?, 
            namePersonPer=? where idDayOff=?";
            $conn = connect_db();
            $stm = $conn->prepare($sql);
            $stm->bind_param("iiiisisi", $dayOffUsed, $dayOffWant, $dayOffLeft, $idStatus, $Status, $idPersonPer, $namePersonPer, $id);   
            if (!$stm->execute()) {
                die('can not execute: ' . $stm->error);
            }
   
            // update bên account
            $sql_2 = "UPDATE account  SET dayOffUsed=?, dayOffLeft=? where id=?";
            $conn = connect_db();
            $stm = $conn->prepare($sql_2);
            $stm->bind_param("iii", $dayOffUsedA, $dayOffLeft, $idPerson);
                
            if (!$stm->execute()) {
                die('can not execute: ' . $stm->error);
            }

            header("location: list-nghiphep.php");
            exit();
        }else{
            header("location: <?php echo 'details-nghiephep.php?id='.$id; ?>");
            exit();
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
	<link rel="stylesheet" href="../style.css">
	<title>APPROVE DAY-OFF</title>
</head>
<body>
    <?php include '../../partial/header.php'?>
	<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <h3 class="text-center mt-3 mb-3">APPROVE DAY-OFF</h3>
            <form method="POST" action="" class="border rounded w-100 mb-5 mx-auto px-3 pt-3 bg-light">
                <p class="alert alert-success">Bạn chắc chắn muốn duyệt đơn nghỉ phép có mã <?= $_GET['id'] ?> không?</p>
                <div class="form-group">
                    <!-- <label for="id">Mã phòng ban:</label> -->
                    <input type="hidden" name="id" class="form-control" value="<?= $_GET['id'] ?>">
                </div>
                <div class="form-group text-center">
                    <input type="submit" class="btn btn-success" value="Duyệt">
                    <a href="<?php echo 'details-nghiphep.php?id='.$id; ?>" class="btn btn-secondary">Quay lại</a>
                </div>
            </form>
        </div>
    </div>
	</div>

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
	<script src="https://kit.fontawesome.com/1542f0c587.js" crossorigin="anonymous"></script>
	<script src="/main.js"></script> 
</body>
</html>