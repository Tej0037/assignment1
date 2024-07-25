<?php
// Include config file
require_once "config.php";

// Define variables and initialize with empty values
$name = $address = $contact = $qualification = "";
$name_err = $address_err = $contact_err = $qualification_err = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if ID is set
    if (isset($_POST["id"]) && !empty($_POST["id"])) {
        $id = $_POST["id"];

        // Validate name
        $input_name = trim($_POST["name"]);
        if (empty($input_name)) {
            $name_err = "Please enter a name.";
        } elseif (!filter_var($input_name, FILTER_VALIDATE_REGEXP, array("options" => array("regexp" => "/^[a-zA-Z\s]+$/")))) {
            $name_err = "Please enter a valid name.";
        } else {
            $name = $input_name;
        }

        // Validate address
        $input_address = trim($_POST["address"]);
        if (empty($input_address)) {
            $address_err = "Please enter an address.";
        } else {
            $address = $input_address;
        }

        // Validate contact
        $input_contact = trim($_POST["contact"]);
        if (empty($input_contact)) {
            $contact_err = "Please enter the contact number.";
        } elseif (!ctype_digit($input_contact)) {
            $contact_err = "Please enter a positive integer value.";
        } else {
            $contact = $input_contact;
        }

        // Validate qualification
        $input_qualification = trim($_POST["qualification"]);
        if (empty($input_qualification)) {
            $qualification_err = "Please enter your qualification.";
        } else {
            $qualification = $input_qualification;
        }

        // Check input errors before updating the database
        if (empty($name_err) && empty($address_err) && empty($contact_err) && empty($qualification_err)) {
            // Prepare an update statement
            $sql = "UPDATE employees SET name=?, address=?, contact=?, qualification=? WHERE id=?";

            if ($stmt = mysqli_prepare($link, $sql)) {
                // Bind variables to the prepared statement as parameters
                mysqli_stmt_bind_param($stmt, "ssssi", $param_name, $param_address, $param_contact, $param_qualification, $param_id);

                // Set parameters
                $param_name = $name;
                $param_address = $address;
                $param_contact = $contact;
                $param_qualification = $qualification;
                $param_id = $id;

                // Attempt to execute the prepared statement
                if (mysqli_stmt_execute($stmt)) {
                    // Records updated successfully. Redirect to landing page
                    header("location: main.php");
                    exit();
                } else {
                    echo "Oops! Something went wrong. Please try again later.";
                }
            }

            // Close statement
            mysqli_stmt_close($stmt);
        }

        // Close connection
        mysqli_close($link);
    } else {
        // ID parameter is not present in the URL. Redirect to error page
        header("location: error.php");
        exit();
    }
} else {
    // Check existence of id parameter before processing further
    if (isset($_GET["id"]) && !empty(trim($_GET["id"]))) {
        // Get URL parameter
        $id = trim($_GET["id"]);

        // Prepare a select statement
        $sql = "SELECT * FROM employees WHERE id = ?";
        if ($stmt = mysqli_prepare($link, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "i", $param_id);

            // Set parameters
            $param_id = $id;

            // Attempt to execute the prepared statement
            if (mysqli_stmt_execute($stmt)) {
                $result = mysqli_stmt_get_result($stmt);

                if (mysqli_num_rows($result) == 1) {
                    /* Fetch result row as an associative array. Since the result set
                    contains only one row, we don't need to use while loop */
                    $row = mysqli_fetch_array($result, MYSQLI_ASSOC);

                    // Retrieve individual field value
                    $name = $row["name"];
                    $address = $row["address"];
                    $contact = $row["contact"];
                    $qualification = $row["qualification"];
                } else {
                    // URL doesn't contain valid id. Redirect to error page
                    header("location: error.php");
                    exit();
                }
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }
        }

        // Close statement
        mysqli_stmt_close($stmt);

        // Close connection
        mysqli_close($link);
    } else {
        // URL doesn't contain id parameter. Redirect to error page
        header("location: error.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Update Record</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .wrapper {
            width: 600px;
            margin: 0 auto;
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <h2 class="mt-5">Update Record</h2>
                    <p>Please edit the input values and submit to update the employee record.</p>
                    <form action="<?php echo htmlspecialchars(basename($_SERVER['REQUEST_URI'])); ?>" method="post">
                        <div class="form-group">
                            <label>Name</label>
                            <input type="text" name="name" class="form-control <?php echo (!empty($name_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $name; ?>">
                            <span class="invalid-feedback"><?php echo $name_err; ?></span>
                        </div>
                        <div class="form-group">
                            <label>Address</label>
                            <textarea name="address" class="form-control <?php echo (!empty($address_err)) ? 'is-invalid' : ''; ?>"><?php echo $address; ?></textarea>
                            <span class="invalid-feedback"><?php echo $address_err; ?></span>
                        </div>
                        <div class="form-group">
                            <label>Contact</label>
                            <input type="text" name="contact" class="form-control <?php echo (!empty($contact_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $contact; ?>">
                            <span class="invalid-feedback"><?php echo $contact_err; ?></span>
                        </div>
                        <div class="form-group">
                            <label>Qualification</label>
                            <input type="text" name="qualification" class="form-control <?php echo (!empty($qualification_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $qualification; ?>">
                            <span class="invalid-feedback"><?php echo $qualification_err; ?></span>
                        </div>
                        <input type="hidden" name="id" value="<?php echo $id; ?>" />
                        <input type="submit" class="btn btn-primary" value="Submit">
                        <a href="main.php" class="btn btn-secondary ml-2">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
