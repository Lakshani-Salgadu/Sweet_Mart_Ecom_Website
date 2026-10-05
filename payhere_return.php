<?php
// payhere_return.php

session_start();

require_once __DIR__ . "/includes/db.php";
require_once __DIR__ . "/includes/functions.php";

// ==================================================
// 1. CHECK USER LOGIN
// ==================================================

$userId = $_SESSION["user_id"] ?? null;

if (!$userId) {
    header("Location: login.php");
    exit;
}


// ==================================================
// 2. GET ORDER ID
// ==================================================

$orderId = filter_var(
    $_GET["order_id"] ?? ($_SESSION["last_order_id"] ?? 0),
    FILTER_VALIDATE_INT
);

$paymentStatus = null;
$orderStatus   = null;


// ==================================================
// 3. GET PAYMENT + ORDER STATUS
// ==================================================

if ($orderId && $orderId > 0) {

    // Only allow logged-in customer to view
    // their own order/payment information.
    $stmt = $conn->prepare(
        "SELECT
            o.status AS order_status,
            p.status AS payment_status
         FROM orders o
         LEFT JOIN payments p ON p.order_id = o.id
         WHERE o.id = ?
           AND o.user_id = ?
         ORDER BY p.id DESC
         LIMIT 1"
    );

    $stmt->bind_param("ii", $orderId, $userId);
    $stmt->execute();

    $result = $stmt->get_result()->fetch_assoc();

    if ($result) {
        $paymentStatus = $result["payment_status"];
        $orderStatus   = $result["order_status"];
    }

    $stmt->close();
}


// ==================================================
// 4. CHECK WHETHER PAYMENT IS PAID
// ==================================================

$isPaid = ($paymentStatus === "Paid");


// ==================================================
// 5. PAGE TITLE
// ==================================================

$pageTitle = $isPaid
    ? "Thank You"
    : "Payment Return";

require __DIR__ . "/includes/header.php";
?>


<!-- ================================================= -->
<!-- PAYHERE RETURN / THANK YOU PAGE                    -->
<!-- ================================================= -->

<section
    class="section-pad"
    style="
        min-height:70vh;
        display:flex;
        align-items:center;
        background:#fffaf6;
    "
>

    <div class="container text-center">


        <!-- ========================================= -->
        <!-- ICON                                      -->
        <!-- ========================================= -->

        <div
            style="
                font-size:5rem;
                margin-bottom:1rem;
            "
        >

            <?php if ($isPaid): ?>

                &#9989;

            <?php elseif ($paymentStatus === "Pending"): ?>

                &#9203;

            <?php else: ?>

                &#128722;

            <?php endif; ?>

        </div>


        <!-- ========================================= -->
        <!-- SUCCESSFUL PAYMENT                        -->
        <!-- ========================================= -->

        <?php if ($isPaid): ?>

            <h1
                class="section-title"
                style="
                    color:var(--pink);
                    margin-bottom:1rem;
                "
            >
                Thank You for Your Order!
            </h1>


            <div
                style="
                    color:#7a5a4a;
                    font-size:1.1rem;
                    max-width:600px;
                    margin:0 auto 2rem;
                    line-height:1.8;
                "
            >

                <p
                    style="
                        font-size:1.25rem;
                        font-weight:600;
                        margin-bottom:1rem;
                    "
                >
                    Your order has been successfully placed! 🎉
                </p>


                <!-- ORDER NUMBER -->

                <?php if ($orderId): ?>

                    <div
                        style="
                            background:#ffffff;
                            border:1px solid #ead8cd;
                            border-radius:14px;
                            padding:16px 25px;
                            margin:20px auto;
                            max-width:350px;
                            box-shadow:0 4px 15px rgba(74,40,22,0.05);
                        "
                    >

                        <span
                            style="
                                color:#9b7968;
                                font-size:.95rem;
                            "
                        >
                            Order Number
                        </span>

                        <br>

                        <strong
                            style="
                                font-size:1.4rem;
                                color:#4a2816;
                            "
                        >
                            #<?= htmlspecialchars((string)$orderId) ?>
                        </strong>

                    </div>

                <?php endif; ?>


                <p>
                    Your payment has been successfully confirmed
                    and recorded as
                    <strong>Paid</strong>.
                </p>


                <p>
                    Thank you for shopping with
                    <strong>Sweet Mart</strong>.
                    We are preparing your sweet treats with care. 🍰
                </p>


                <p>
                    You can view your order details and check
                    the latest order status from the
                    <strong>My Orders</strong> page.
                </p>

            </div>


        <!-- ========================================= -->
        <!-- PENDING PAYMENT                           -->
        <!-- ========================================= -->

        <?php elseif ($paymentStatus === "Pending"): ?>

            <h1
                class="section-title"
                style="
                    color:var(--pink);
                    margin-bottom:1rem;
                "
            >
                Returned from PayHere
            </h1>


            <div
                style="
                    color:#7a5a4a;
                    font-size:1.1rem;
                    max-width:570px;
                    margin:.5rem auto 1.5rem;
                    line-height:1.8;
                "
            >

                <!-- ORDER NUMBER -->

                <?php if ($orderId): ?>

                    <p>
                        Order:
                        <strong>
                            #<?= htmlspecialchars((string)$orderId) ?>
                        </strong>
                    </p>

                <?php endif; ?>


                <p>
                    Your payment is currently
                    <strong>Pending verification</strong>.
                </p>


                <p>
                    Returning from PayHere does not confirm
                    that the payment was successful.
                    The payment status will normally be updated
                    after a verified notification is received
                    from PayHere.
                </p>

            </div>


            <!-- ===================================== -->
            <!-- LOCALHOST SANDBOX TEST MODE           -->
            <!-- ===================================== -->

            <div
                style="
                    max-width:520px;
                    margin:25px auto 30px;
                    padding:20px 25px;
                    background:#fff8e5;
                    border:1px solid #ead7a4;
                    border-radius:14px;
                    color:#6b5138;
                "
            >

                <div
                    style="
                        font-size:1.05rem;
                        font-weight:700;
                        margin-bottom:8px;
                        color:#4a2816;
                    "
                >
                    🧪 Sandbox Testing Mode
                </div>


                <p
                    style="
                        margin:0 0 15px;
                        font-size:.9rem;
                        line-height:1.6;
                    "
                >
                    This project is currently running on localhost,
                    so PayHere cannot send its server notification
                    directly to this computer.

                    Use the button below only to demonstrate the
                    successful payment and order confirmation flow
                    during local development.
                </p>


                <!-- SANDBOX CONFIRM FORM -->

                <form
                    method="POST"
                    action="sandbox_confirm_payment.php"
                    style="margin:0;"
                >

                    <input
                        type="hidden"
                        name="order_id"
                        value="<?= htmlspecialchars((string)$orderId) ?>"
                    >

                    <button
                        type="submit"
                        class="btn-primary-sm"
                        style="
                            border:none;
                            cursor:pointer;
                        "
                    >
                        Confirm Sandbox Test Payment
                    </button>

                </form>

            </div>


        <!-- ========================================= -->
        <!-- UNKNOWN PAYMENT STATUS                    -->
        <!-- ========================================= -->

        <?php else: ?>

            <h1
                class="section-title"
                style="
                    color:var(--pink);
                    margin-bottom:1rem;
                "
            >
                Payment Status
            </h1>


            <div
                style="
                    color:#7a5a4a;
                    font-size:1.1rem;
                    max-width:550px;
                    margin:.5rem auto 2rem;
                    line-height:1.8;
                "
            >

                <p>
                    We could not determine the payment status
                    for this order.
                </p>


                <?php if ($orderId): ?>

                    <p>
                        Order:
                        <strong>
                            #<?= htmlspecialchars((string)$orderId) ?>
                        </strong>
                    </p>

                <?php endif; ?>


                <p>
                    Please open
                    <strong>My Orders</strong>
                    to check your order and payment details.
                </p>

            </div>

        <?php endif; ?>



        <!-- ========================================= -->
        <!-- ACTION BUTTONS                            -->
        <!-- ========================================= -->

        <div
            class="mt-4 d-flex gap-3 justify-content-center flex-wrap"
        >

            <a
                href="my_orders.php"
                class="btn-primary-sm"
            >
                <?= $isPaid
                    ? "View My Order"
                    : "Check Order Status"
                ?>
            </a>


            <a
                href="shop.php"
                class="btn-outline-brown"
            >
                Continue Shopping
            </a>

        </div>

    </div>

</section>


<?php
require __DIR__ . "/includes/footer.php";
?>