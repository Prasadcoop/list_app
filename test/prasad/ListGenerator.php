<?php

class ListGenerator {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function generateList() {
        return $this->fetchMembers();
    }

    private function fetchMembers() {
        try {
            $sql = "SELECT id, name, parentid FROM member_list";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $members = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $members[] = $row;
            }
            return $members;
        } catch(PDOException $e) {
            return array("error" => $e->getMessage());
        }
    }

    public function addMember($name, $parentId = NULL) {
        try {
            $name = htmlspecialchars($name);
            if (!isset($parentId)) {
                $parentId = null;
            }
            $parentId = $parentId ? $parentId : null;
            $sql = "INSERT INTO member_list (name, parentid) VALUES (:name, :parentid)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':parentid', $parentId);
            if ($stmt->execute()) {
                return true;
            } else {
                return false;
            }
        } catch (PDOException $e) {
            return false; 
        }
    }

    // not use function
    public function dropdownlist() {
        try {
            $sql = "SELECT id, name FROM member_list";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $membersDropdown = "";
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $membersDropdown .= "<option value='" . $row['id'] . "'>" . $row['name'] . "</option>";
            }
            return $membersDropdown;
        } catch(PDOException $e) {
            return ""; 
        }
    }

   
    public function getDropdownData() {
        $dropdownData = array();
        try {
            $stmt = $this->conn->prepare("SELECT id, name FROM member_list");
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_OBJ);
            foreach ($results as $row) {
                $dropdownData[] = $row;
            }
            $stmt->closeCursor();
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }

        return $dropdownData;
    }

 
    
}

?>
