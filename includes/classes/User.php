<?php
class User {

    private $con, $sqlData;

    public function __construct($con, $username) {
        $this->con = $con;

        $query = $this->con->prepare("SELECT * FROM users WHERE username = :un");
        $query->bindParam(":un", $username);
        $query->execute();

        // Check if data is present before fetching
        if ($query->rowCount() > 0) {
            $this->sqlData = $query->fetch(PDO::FETCH_ASSOC);
        } else {
            // Handle the case where the user is not found (not logged in)
            $this->sqlData = false;
        }
    }

    public static function isLoggedIn() {
        return isset($_SESSION["userLoggedIn"]);
    }
    
    // Add a check for $this->sqlData before accessing its elements

    public function getUsername() {
        return $this->sqlData && isset($this->sqlData["username"]) ? $this->sqlData["username"] : null;
    }

    public function getName() {
        return $this->sqlData && isset($this->sqlData["firstName"]) && isset($this->sqlData["lastName"]) ? $this->sqlData["firstName"] . " " . $this->sqlData["lastName"] : null;
    }

    public function getFirstName() {
        return $this->sqlData && isset($this->sqlData["firstName"]) ? $this->sqlData["firstName"] : null;
    }

    public function getLastName() {
        return $this->sqlData && isset($this->sqlData["lastName"]) ? $this->sqlData["lastName"] : null;
    }

    public function getEmail() {
        return $this->sqlData && isset($this->sqlData["email"]) ? $this->sqlData["email"] : null;
    }

    public function getProfilePic() {
        return $this->sqlData && isset($this->sqlData["profilePic"]) ? $this->sqlData["profilePic"] : null;
    }

    public function getSignUpDate() {
        return $this->sqlData && isset($this->sqlData["signUpDate"]) ? $this->sqlData["signUpDate"] : null;
    }

    public function isSubscribedTo($userTo) {
        if (!$this->sqlData) {
            return false;
        }
    
        $username = $this->getUsername(); // Store the value in a variable
        $query = $this->con->prepare("SELECT * FROM subscribers WHERE userTo=:userTo AND userFrom=:userFrom");
        $query->bindParam(":userTo", $userTo);
        $query->bindParam(":userFrom", $username); // Pass the variable by reference
        $query->execute();
        return $query->rowCount() > 0;
    }
    

    public function getSubscriberCount() {
        if (!$this->sqlData) {
            return 0;
        }
    
        $username = $this->getUsername(); // Store the value in a variable
        $query = $this->con->prepare("SELECT * FROM subscribers WHERE userTo=:userTo");
        $query->bindValue(":userTo", $username); // Use bindValue to pass the value directly
        $query->execute();
        return $query->rowCount();
    }

    public function getSubscriptions() {
        if (!$this->sqlData) {
            return array();
        }
    
        $username = $this->getUsername(); // Store the value in a variable
        $query = $this->con->prepare("SELECT userTo FROM subscribers WHERE userFrom=:userFrom");
        $query->bindParam(":userFrom", $username); // Pass the variable by reference
        $query->execute();
    
        $subs = array();
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $user = new User($this->con, $row["userTo"]);
            array_push($subs, $user);
        }
        return $subs;
    }
    
}
?>
