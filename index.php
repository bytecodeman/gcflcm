<?php
error_reporting(E_ALL | E_ERROR | E_WARNING | E_PARSE | E_NOTICE);

spl_autoload_register(function ($class_name) {
    require_once $class_name . '.php';
});

require "../library/php/dbconnect.php";
require "../library/php/library.php";

function logAccess($expr)
{
    try {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME;
        $pdo = new PDO($dsn, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $pdo->beginTransaction();
        $sql = 'INSERT INTO gcflcm(expr, ipaddr) VALUES(:expr, :ipaddr)';
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['expr' => $expr, 'ipaddr' => getUserIP()]);
        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        echo 'Connection failed: ' . $e->getMessage();
    }
}

function sanitize_input($str)
{ // Override the function from the library
    return preg_replace("/[?'&<>\"]/", "", $str);
}

function gcf($m, $n)
{
    $m = gmp_abs($m);
    $n = gmp_abs($n);
    if (gmp_cmp($n, 0) == -0)
        return $m;
    while (gmp_cmp(($rem = gmp_div_r($m, $n)), 0) != 0) {
        $m = $n;
        $n = $rem;
    }
    return $n;
}


$title = "GCF/LCM Calculator!";
$current = "gcflcm";
$errMsg = "";
$success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') :
    $expressions = array();
    foreach ($_POST["expr"] as $key => $value) {
        $tmpexpr = sanitize_input($value);
        if ($value !== $tmpexpr) {
            $errMsg = "Hacking Attempt Detected";
            break;
        }
        $value = trim($value);
        if ($value !== "" && is_numeric($value)) {
            $expressions[] = $value;
        }
    }

    if ($errMsg === "") {
        $count = count($expressions);
        if ($count <= 0) {
            $errMsg .= "No Numbers Specified";
        } else {
            $number = $expressions[0];
            $numbers = $number;
            $gcf = $number;
            $lcm = $number;
            for ($i = 1; $i < $count; $i++) {
                $prevnumber = $lcm;
                $number = $expressions[$i];
                $numbers .= ", " . $number;
                $gcf = gcf($gcf, $number);

                $product = gmp_mul($prevnumber, $number);
                $productgcf = gcf($prevnumber, $number);
                $lcm = gmp_div($product, $productgcf);
            }
            $success = true;
            logAccess($numbers);
        }
    }
endif;
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title><?php echo $title; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="author" content="Antonio C. Silvestri">
    <meta name="description" content="Calculates the Greatest Common Factor (GCF) and Lowest Common Multiple (LCM) of multiple numbers.">
    <link rel="stylesheet" href="//stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <link rel="stylesheet" href="//use.fontawesome.com/releases/v5.7.2/css/all.css" integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="shortcut icon" type="image/x-icon" href="/specialapps/lcm/img/favicon.ico">

    <meta name="twitter:card" content="summary">
    <meta name="twitter:site" content="@bytecodeman">
    <meta name="twitter:title" content="<?php echo $title; ?>">
    <meta name="twitter:description" content="Calculates the Greatest Common Factor (GCF) and Lowest Common Multiple (LCM) of multiple numbers.">
    <meta name="twitter:image" content="https://cs.stcc.edu/specialapps/lcm/img/lcmicon.png">

    <meta property="og:url" content="https://cs.stcc.edu/specialapps/lcm/">
    <meta property="og:type" content="article">
    <meta property="og:title" content="<?php echo $title; ?>">
    <meta property="og:description" content="Calculates the Greatest Common Factor (GCF) and Lowest Common Multiple (LCM) of multiple numbers.">
    <meta property="og:image" content="https://cs.stcc.edu/specialapps/lcm/img/lcmicon.png">

    <script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
    <script>
        (adsbygoogle = window.adsbygoogle || []).push({
            google_ad_client: "ca-pub-9626577709396562",
            enable_page_level_ads: true
        });
    </script>
</head>

<body>
    <?php include "../library/php/navbar.php"; ?>
    <div class="container">
        <div class="jumbotron">
            <div class="row">
                <div class="col-lg-8">
                    <h1><?php echo $title; ?></h1>
                    <p>This system calculates the Greatest Common Factor (GCF) and Lowest Common Multiple (LCM) of multiple numbers.</p>
                    <p>Enter numbers in any order, in any combination, in a maximum of 5 text boxes.</p>
                    <p class="d-print-none"><a href="#" data-toggle="modal" data-target="#myModal">About <?php echo $title; ?></a></p>
                    <p class="d-print-none"><a href="https://github.com/bytecodeman/gcflcm" target="_blank" rel="noopener noreferrer">Source Code</a></p>
                </div>
                <div class="col-lg-4 d-print-none">
                    <ins class="adsbygoogle" style="display:block" data-ad-client="ca-pub-9626577709396562" data-ad-slot="7064413444" data-ad-format="auto"></ins>
                    <script>
                        (adsbygoogle = window.adsbygoogle || []).push({});
                    </script>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <?php if (!empty($errMsg)) : ?>
                    <div id="errMsg" class="form-group text-danger text-center font-weight-bold h3">
                        <?php echo $errMsg; ?>
                    </div>
                <?php endif; ?>
                <?php if ($success) : ?>
                    <fieldset class="LCM">
                        <legend class="text-success">GCF/LCM Found!!!
                            <div id="copyToClipboard">
                                <a tabindex="0" id="copytoclip" data-trigger="focus" data-clipboard-target="#LCMOutput" data-container="body" data-toggle="popover" data-placement="bottom" data-content="Copied!">
                                    <img src="img/clippy.svg" alt="Copy to Clipboard" title="Copy to Clipboard">
                                </a>
                            </div>
                        </legend>
                        <div id="LCMOutput"><?php echo "Numbers: " . $numbers . "<br>GCF:     " . $gcf . "<br>LCM:     " . $lcm; ?></div>
                    </fieldset>
                <?php endif; ?>
                <div class="card">
                    <div id="main" class="card-body">
                        <h2 class="title">Numbers <small class="text-muted">(Max: 5)</small></h2>
                        <form id="lcmform" method="post" action="<?php echo htmlspecialchars(extractPath($_SERVER["PHP_SELF"])); ?>">
                            <ul id="items" class="list-group">
                                <li class="list-group-item">
                                    <div class="input-group">
                                        <input type="number" min="1" name="expr[]" class="form-control expression" placeholder="" aria-label="" autofocus>
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-danger btn-lg float-right delete" title="Delete Number"><i class="fas fa-trash-alt"></i></button>
                                        </div>
                                    </div>
                                </li>
                                <li class="list-group-item">
                                    <div class="input-group">
                                        <input type="number" min="1" name="expr[]" class="form-control expression" placeholder="" aria-label="">
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-danger btn-lg float-right delete" title="Delete Number"><i class="fas fa-trash-alt"></i></button>
                                        </div>
                                    </div>
                                </li>
                                <li class="list-group-item">
                                    <div class="input-group">
                                        <input id="newExpr" type="number" min="1" class="form-control" placeholder="Add Additional Number" aria-label="Add Additional Expression">
                                        <div class="input-group-append">
                                            <button type="button" id="addExpr" class="btn btn-success btn-lg float-right" title="Add Additional Number"><i class="fas fa-plus"></i></button>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                            <button type="submit" id="submit" name="submit" class="btn btn-primary btn-lg mt-5">Submit</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php
    require "../library/php/about.php";
    ?>

    <script src="//code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="//stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
    <script src="//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-5a576c39d176f4a6"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/1.7.1/clipboard.min.js"></script>
    <script>
        $(function() {
            function adjustInputAttributes() {
                $("#items li").each(function() {
                    const text = "Enter Number " + ($(this).index() + 1);
                    $("input.form-control.expression", this).prop("placeholder", text).attr("aria-label", text);
                });
            };

            $('[data-toggle="popover"]').popover();
            new Clipboard("#copytoclip");
            adjustInputAttributes();

            $("#lcmform input.expression").on('keypress', function(e) {
                return e.which !== 13;
            });

            $("#newExpr").on('keypress', function(e) {
                if (e.which === 13) {
                    $("#addExpr").click();
                    return false;
                }
                return true;
            });

            // Deletes an Expression
            $('#items').on("click", "button.delete", function(e) {
                if ($("#items input.expression").length <= 1) {
                    return false;
                }
                const $target = $(e.target);

                $target.closest("li").remove();
                $("#addExpr").closest("li").show();

                adjustInputAttributes();
                return false;
            });

            // Adds an Expression
            $("#addExpr").click(function(e) {
                const expr = $("#newExpr").val().trim();
                if (expr === "") {
                    return false;
                }

                const $listitem =
                    $(`
                    <li class="list-group-item">
                        <div class="input-group">
                            <input type="number" min="1" name="expr[]" class="form-control expression" placeholder="" aria-label="">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-danger btn-lg float-right delete" title="Delete Expression"><i class="fas fa-trash-alt"></i></button>
                            </div>
                        </div>
                    </li>`);

                $("input", $listitem).val(expr);
                $("#newExpr").val("");

                $listitem.insertBefore("#items li:last-child");
                if ($("#items input.expression").length >= 5) {
                    $("#addExpr").closest("li").hide();
                }

                adjustInputAttributes();
                return false;
            });


            $("#lcmform").submit(function() {
                $("#addExpr:visible").click();
                $("#submit").html('Please Wait <i class="fas fa-spinner fa-spin fa-lg ml-3"></i>').attr("disabled", "disabled");
                return true;
            });

        });
    </script>
</body>

</html>