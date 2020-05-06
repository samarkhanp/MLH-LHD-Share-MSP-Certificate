<?php
$servername = "";
$username = "";
$password = "";
$dbname = "";

$conn = mysqli_connect($servername, $username, $password, $dbname) or die("Connection failed: " . mysqli_connect_error());

/* check connection */
if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}

class Database {
    private $db_host = ''; // Database Host
    private $db_user = ''; // Username
    private $db_pass = ''; // Password
    private $db_name = ''; // Database
    private $con = false; // Checks to see if the connection is active
    private $result = array(); // Results that are returned from the query
    private $myconn;
    private $numResults;

    public function connect() {
        $this->myconn = @mysqli_connect($this->db_host, $this->db_user, $this->db_pass, $this->db_name);
        if (mysqli_connect_errno()) {
            echo "Failed to connect to MySQL: " . mysqli_connect_error();
            exit();
        } else {
            $this->con = true;
            return $this->myconn;
        }
    }

    /*
     * Changes the new database, sets all current results
     * to null
     */

    public function setDatabase($name) {
        if ($this->con) {
            if (mysqli_close()) {
                $this->con = false;
                $this->results = null;
                $this->db_name = $name;
                $this->connect();
            }
        }
    }

    /*
     * Checks to see if the table exists when performing
     * queries
     */

    public function tableExists($table) {
        $tablesInDb = @mysqli_query($this->myconn, 'SHOW TABLES FROM ' . $this->db_name . ' LIKE "' . $table . '"');
        if ($tablesInDb) {
            if (mysqli_num_rows($tablesInDb) == 1) {
                // echo "Found Table";
                return true;
            } else {
                // echo "Found Table No";
                return false;
            }
        }
    }

    /*
     * Selects information from the database.
     * Required: table (the name of the table)
     * Optional: rows (the columns requested, separated by commas)
     * where (column = value as a string)
     * order (column DIRECTION as a string)
     */

    public function selectCount($table, $rows = '*', $where = null, $order = null) {
        $q = 'SELECT ' . $rows . ' FROM ' . $table;
        if ($where != null)
            $q .= ' WHERE ' . $where;
        if ($order != null)
            $q .= ' ORDER BY ' . $order;
//         echo "<b style='color:red;'><br/>".$q."</b>";
        $query = @mysqli_query($q);
        $this->result = array(); // changed by praveen
        if ($query) {
            $this->numResults = mysqli_num_rows($query);
        }
        return $this->numResults;
    }

    public function select($table, $rows = '*', $where = null, $order = null) {
        $sql = 'SELECT ' . $rows . ' FROM ' . $table;

        if ($where != null)
            $sql .= ' WHERE ' . $where;
        if ($order != null)
            $sql .= ' ORDER BY ' . $order;
      //  echo "<br/><b style='color:green;'>".$sql."</b>";
        $result = mysqli_query($this->myconn, $sql);
        $this->result = [];
        if (mysqli_num_rows($result) > 0) {
            // output data of each row
            while ($row = mysqli_fetch_assoc($result)) {
                $this->result [] = $row;
            }
        } else {
            $this->result = [];
        }
        return $this->result;
    }

    /*
     * Insert values into the table
     * Required: table (the name of the table)
     * values (the values to be inserted)
     * Optional: rows (if values don't match the number of rows)
     */

    public function getRecCount($table, $id) {
        $qc = "select max($id) from $table";
        $res = mysqli_query($this->myconn, $qc);
        $rows = mysqli_fetch_array($res);
        $rc = $rows [0];
        return $rc;
    }

    public function getRecCountUser($table, $id, $cond = null) {
        if ($cond != null)
            $qc = "select count($id) from $table where $cond";
        else
            $qc = "select count($id) from $table";
        // echo $qc."<br/>";
        $res = mysqli_query($this->myconn, $qc);
        $rows = mysqli_fetch_array($res);
        $rc = $rows [0];
        return $rc;
    }

    public function checkExistingRec($table, $condition = null) {
        if ($condition != null)
            $qc = "select count(*) from $table where $condition";
        else
            $qc = "select count(*) from $table";
        // echo $qc . "<br/>";
        $res = mysqli_query($this->myconn, $qc);
        $rows = mysqli_fetch_array($res);
        $rc = $rows [0];
        return $rc;
    }

    public function insert($table, $values, $rows = null) {
        if ($this->tableExists($table)) {
            $insert = 'INSERT INTO ' . $table;
            if ($rows != null) {
                $insert .= ' (' . $rows . ')';
            }

            for ($i = 0; $i < count($values); $i ++) {
                if (is_string($values [$i]))
                    $values [$i] = '"' . $values [$i] . '"';
            }
            $values = implode(',', $values);
            $insert .= ' VALUES (' . $values . ')';
            //echo "<br/><b style='color:blue;'>".$insert."</b>";
            $ins = @mysqli_query($this->myconn, $insert);

            if ($ins) {
                return true;
            } else {
                return false;
            }
        }
    }

    /*
     * Deletes table or records where condition is true
     * Required: table (the name of the table)
     * Optional: where (condition [column = value])
     */

    public function delete($table, $where = null) {
        if ($this->tableExists($table)) {
            if ($where == null) {
                $delete = 'DELETE ' . $table;
            } else {
                $delete = 'DELETE FROM ' . $table . ' WHERE ' . $where;
            }
            $del = @mysqli_query($this->myconn, $delete);

            if ($del) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /*
     * Updates the database with the values sent
     * Required: table (the name of the table to be updated
     * rows (the rows/values in a key/value array
     * where (the row/condition in an array (row,condition) )
     */

    public function update($table, $rows, $where) {
        if ($this->tableExists($table)) {
            // Parse the where values
            // even values (including 0) contain the where rows
            // odd values contain the clauses for the row
            for ($i = 0; $i < count($where); $i ++) {
                if ($i % 2 != 0) {
                    if (is_string($where [$i])) {
                        if (($i + 1) != null)
                            $where [$i] = '"' . $where [$i] . '" AND ';
                        else
                            $where [$i] = '"' . $where [$i] . '"';
                    }
                }
            }
            $where = implode('', $where);

            $update = 'UPDATE ' . $table . ' SET ';
            $keys = array_keys($rows);
            for ($i = 0; $i < count($rows); $i ++) {
                if (is_string($rows [$keys [$i]])) {
                    $update .= $keys [$i] . '="' . $rows [$keys [$i]] . '"';
                } else {
                    $update .= $keys [$i] . '=' . $rows [$keys [$i]];
                }

                // Parse to add commas
                if ($i != count($rows) - 1) {
                    $update .= ',';
                }
            }
            $update .= ' WHERE ' . $where;
            //echo "<br/>" . $update;
            $query = @mysqli_query($this->myconn, $update);
            if ($query) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /*
     * Returns the result set
     */

    public function getResult() {
        return $this->result;
    }

}
?>
