<?php
require_once __DIR__ . "/includes/db.php";
require_once __DIR__ . "/includes/functions.php";

$pageTitle = "Login / Register";

if (isLoggedIn()) {
    header("Location: index.php");
    exit;
}

$tab = $_GET["tab"] ?? "login";
$redirect = $_GET["redirect"] ?? $_POST["redirect"] ?? "";
$safeRedirect = "";

if ($redirect) {
    $parsed = parse_url($redirect, PHP_URL_PATH);

    $allowed = [
        "checkout.php",
        "profile.php",
        "my_orders.php",
        "cart.php",
        "custom_box.php",
        "shop.php",
        "index.php"
    ];

    if (in_array(basename($parsed), $allowed)) {
        $safeRedirect = $redirect;
    }
}

$errors = [];


// ==================================================
// LOGIN HANDLER
// ==================================================

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["action_login"])
) {

    $email = trim($_POST["email"] ?? "");
    $pass  = $_POST["password"] ?? "";

    if (!$email || !$pass) {

        $errors[] = "Please enter both email and password.";
        $tab = "login";

    } else {

        $stmt = $conn->prepare(
            "SELECT * FROM users WHERE email = ?"
        );

        $stmt->bind_param("s", $email);
        $stmt->execute();

        $user = $stmt
            ->get_result()
            ->fetch_assoc();

        $stmt->close();

        $storedHash =
            $user["password_hash"]
            ?? $user["password"]
            ?? "";

        if (
            $user &&
            $storedHash &&
            password_verify($pass, $storedHash)
        ) {

            // Regenerate session ID for security
            session_regenerate_id(true);

            $_SESSION["user_id"] =
                (int)$user["id"];

            $_SESSION["full_name"] =
                $user["full_name"];

            $_SESSION["email"] =
                $user["email"];

            $_SESSION["role"] =
                $user["role"] ?? "customer";


            setFlash(
                "success",
                "Welcome back, " .
                clean($user["full_name"]) .
                "!"
            );


            // Redirect user after login
            if ($safeRedirect) {

                header(
                    "Location: " . $safeRedirect
                );

            } elseif (
                ($user["role"] ?? "customer") === "admin"
            ) {

                header(
                    "Location: admin/index.php"
                );

            } else {

                header(
                    "Location: index.php"
                );
            }

            exit;

        } else {

            // Generic error prevents user enumeration
            $errors[] =
                "Invalid email or password.";

            $tab = "login";
        }
    }
}


// ==================================================
// REGISTER HANDLER
// ==================================================

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["action_register"])
) {

    $fn  = trim($_POST["full_name"] ?? "");
    $em  = trim($_POST["email"] ?? "");
    $ph  = trim($_POST["phone"] ?? "");
    $pw  = $_POST["password"] ?? "";
    $cpw = $_POST["confirm_password"] ?? "";


    // ------------------------------------------------
    // VALIDATION
    // ------------------------------------------------

    if (!$fn) {
        $errors[] =
            "Full name is required.";
    }


    if (
        !$em ||
        !filter_var(
            $em,
            FILTER_VALIDATE_EMAIL
        )
    ) {
        $errors[] =
            "A valid email address is required.";
    }


    if (strlen($pw) < 8) {

        $errors[] =
            "Password must be at least 8 characters long.";

    } elseif (
        !preg_match('/[A-Za-z]/', $pw) ||
        !preg_match('/[0-9]/', $pw)
    ) {

        $errors[] =
            "Password must contain at least one letter and one number.";
    }


    if ($pw !== $cpw) {

        $errors[] =
            "Passwords do not match.";
    }


    // ------------------------------------------------
    // CREATE ACCOUNT
    // ------------------------------------------------

    if (empty($errors)) {

        // Check duplicate email
        $ck = $conn->prepare(
            "SELECT id
             FROM users
             WHERE email = ?"
        );

        $ck->bind_param(
            "s",
            $em
        );

        $ck->execute();


        if (
            $ck->get_result()->num_rows > 0
        ) {

            $errors[] =
                "An account with this email address already exists.";

            $tab = "register";

            $ck->close();

        } else {

            $ck->close();


            // -----------------------------------------
            // HASH PASSWORD USING BCRYPT
            // -----------------------------------------

            $hash = password_hash(
                $pw,
                PASSWORD_BCRYPT
            );


            // -----------------------------------------
            // INSERT USER
            // -----------------------------------------

            $ins = $conn->prepare(
                "INSERT INTO users
                (
                    full_name,
                    email,
                    phone,
                    password,
                    password_hash,
                    role
                )
                VALUES
                (
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    'customer'
                )"
            );

            $ins->bind_param(
                "sssss",
                $fn,
                $em,
                $ph,
                $hash,
                $hash
            );

            $ins->execute();

            $uid = $conn->insert_id;

            $ins->close();


            // -----------------------------------------
            // CREATE / SYNC USER PROFILE
            // -----------------------------------------

            $insProf = $conn->prepare(
                "INSERT INTO user_profiles
                (
                    user_id,
                    phone
                )
                VALUES
                (
                    ?,
                    ?
                )
                ON DUPLICATE KEY UPDATE
                    phone = VALUES(phone)"
            );

            $insProf->bind_param(
                "is",
                $uid,
                $ph
            );

            $insProf->execute();
            $insProf->close();


            // -----------------------------------------
            // SUCCESS MESSAGE
            // -----------------------------------------

            setFlash(
                "success",
                "Account created successfully! Please log in with your credentials."
            );


            $dest =
                "login.php?tab=login"
                .
                (
                    $safeRedirect
                    ? "&redirect=" .
                      urlencode($safeRedirect)
                    : ""
                );


            header(
                "Location: " . $dest
            );

            exit;
        }

    } else {

        $tab = "register";
    }
}


// ==================================================
// HEADER
// ==================================================

require __DIR__ . "/includes/header.php";
?>


<!-- ================================================= -->
<!-- LOGIN / REGISTER PAGE                              -->
<!-- ================================================= -->

<section
    class="section-pad"
    style="
        min-height:70vh;
        background:
        linear-gradient(
            135deg,
            #fff5ef,
            #fce8e8
        );
    "
>

    <div class="container">

        <div class="row justify-content-center">

            <div class="col-lg-5 col-md-7">


                <!-- ================================= -->
                <!-- PAGE HEADER                       -->
                <!-- ================================= -->

                <div class="text-center mb-4">

                    <div style="font-size:3rem;">
                        🍰
                    </div>

                    <h1
                        class="section-title"
                        style="font-size:1.8rem;"
                    >
                        Welcome to Sweet Mart
                    </h1>

                    <p
                        class="section-sub"
                        style="margin-bottom:1.5rem;"
                    >
                        Online Dessert &amp; Gift Store
                    </p>

                </div>


                <!-- ================================= -->
                <!-- AUTHENTICATION CARD               -->
                <!-- ================================= -->

                <div class="auth-card">


                    <!-- ============================= -->
                    <!-- TABS                          -->
                    <!-- ============================= -->

                    <div class="auth-tabs">

                        <div
                            class="auth-tab <?= $tab === 'login' ? 'active' : '' ?>"
                            onclick="switchTab('login')"
                            id="tab-login"
                        >
                            Login
                        </div>


                        <div
                            class="auth-tab <?= $tab === 'register' ? 'active' : '' ?>"
                            onclick="switchTab('register')"
                            id="tab-register"
                        >
                            Register
                        </div>

                    </div>


                    <!-- ============================= -->
                    <!-- ERROR MESSAGES                 -->
                    <!-- ============================= -->

                    <?php foreach ($errors as $e): ?>

                        <div class="alert alert-error">
                            ❌ <?= clean($e) ?>
                        </div>

                    <?php endforeach; ?>


                    <!-- ============================= -->
                    <!-- LOGIN FORM                     -->
                    <!-- ============================= -->

                    <div
                        id="panel-login"
                        <?= $tab === 'register'
                            ? 'style="display:none"'
                            : ''
                        ?>
                    >

                        <form method="POST">


                            <!-- REDIRECT -->

                            <input
                                type="hidden"
                                name="redirect"
                                value="<?= clean($safeRedirect) ?>"
                            >


                            <!-- EMAIL -->

                            <div class="mb-3">

                                <label
                                    class="form-label checkout-form fw-bold"
                                    for="login-email"
                                >
                                    Email Address *
                                </label>

                                <input
                                    name="email"
                                    type="email"
                                    class="form-control"
                                    id="login-email"
                                    required
                                    value="<?= clean($_POST['email'] ?? '') ?>"
                                >

                            </div>


                            <!-- PASSWORD -->

                            <div class="mb-4">

                                <label
                                    class="form-label checkout-form fw-bold"
                                    for="login-password"
                                >
                                    Password *
                                </label>

                                <input
                                    name="password"
                                    type="password"
                                    class="form-control"
                                    id="login-password"
                                    required
                                >

                            </div>


                            <!-- LOGIN BUTTON -->

                            <button
                                type="submit"
                                name="action_login"
                                class="btn-primary-sm w-100"
                                style="padding:13px;"
                                id="login-submit-btn"
                            >
                                Login
                            </button>

                        </form>

                    </div>


                    <!-- ============================= -->
                    <!-- REGISTER FORM                  -->
                    <!-- ============================= -->

                    <div
                        id="panel-register"
                        <?= $tab === 'login'
                            ? 'style="display:none"'
                            : ''
                        ?>
                    >

                        <form method="POST">


                            <!-- REDIRECT -->

                            <input
                                type="hidden"
                                name="redirect"
                                value="<?= clean($safeRedirect) ?>"
                            >


                            <!-- FULL NAME -->

                            <div class="mb-3">

                                <label
                                    class="form-label fw-bold"
                                    for="reg-name"
                                >
                                    Full Name *
                                </label>

                                <input
                                    name="full_name"
                                    class="form-control"
                                    id="reg-name"
                                    required
                                    value="<?= clean($_POST['full_name'] ?? '') ?>"
                                >

                            </div>


                            <!-- EMAIL -->

                            <div class="mb-3">

                                <label
                                    class="form-label fw-bold"
                                    for="reg-email"
                                >
                                    Email Address *
                                </label>

                                <input
                                    name="email"
                                    type="email"
                                    class="form-control"
                                    id="reg-email"
                                    required
                                    value="<?= clean($_POST['email'] ?? '') ?>"
                                >

                            </div>


                            <!-- PHONE -->

                            <div class="mb-3">

                                <label
                                    class="form-label fw-bold"
                                    for="reg-phone"
                                >
                                    Phone Number
                                </label>

                                <input
                                    name="phone"
                                    class="form-control"
                                    id="reg-phone"
                                    placeholder="+94 7X XXX XXXX"
                                    value="<?= clean($_POST['phone'] ?? '') ?>"
                                >

                            </div>


                            <!-- PASSWORD -->

                            <div class="mb-3">

                                <label
                                    class="form-label fw-bold"
                                    for="reg-pass"
                                >
                                    Password *
                                </label>

                                <input
                                    name="password"
                                    type="password"
                                    class="form-control"
                                    id="reg-pass"
                                    required
                                    minlength="8"
                                    pattern="(?=.*[A-Za-z])(?=.*\d).{8,}"
                                    title="At least 8 characters containing letters and numbers"
                                >

                                <div
                                    class="form-text"
                                    style="
                                        font-size:.78rem;
                                        color:#8a6a5a;
                                    "
                                >
                                    Minimum 8 characters with at least
                                    one letter and one number.
                                </div>

                            </div>


                            <!-- CONFIRM PASSWORD -->

                            <div class="mb-4">

                                <label
                                    class="form-label fw-bold"
                                    for="reg-confirm"
                                >
                                    Confirm Password *
                                </label>

                                <input
                                    name="confirm_password"
                                    type="password"
                                    class="form-control"
                                    id="reg-confirm"
                                    required
                                    minlength="8"
                                >

                            </div>


                            <!-- REGISTER BUTTON -->

                            <button
                                type="submit"
                                name="action_register"
                                class="btn-primary-sm w-100"
                                style="padding:13px;"
                                id="register-submit-btn"
                            >
                                Create Account
                            </button>

                        </form>

                    </div>

                </div>

            </div>

        </div>

    </div>

</section>


<!-- ================================================= -->
<!-- TAB SWITCHING                                      -->
<!-- ================================================= -->

<script>

function switchTab(t) {

    document
        .querySelectorAll(".auth-tab")
        .forEach(
            el => el.classList.remove("active")
        );


    const tabEl =
        document.getElementById(
            "tab-" + t
        );


    if (tabEl) {
        tabEl.classList.add("active");
    }


    const pLogin =
        document.getElementById(
            "panel-login"
        );


    const pReg =
        document.getElementById(
            "panel-register"
        );


    if (pLogin) {
        pLogin.style.display =
            t === "login"
                ? "block"
                : "none";
    }


    if (pReg) {
        pReg.style.display =
            t === "register"
                ? "block"
                : "none";
    }
}

</script>


<?php
require __DIR__ . "/includes/footer.php";
?>