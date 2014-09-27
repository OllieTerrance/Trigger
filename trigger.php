<?
$trigFile = getenv("DATA") . "remote_trigger";
$depl = file_exists($trigFile);
// toggle deployment
if (array_key_exists("d", $_GET)) {
    if ($depl) {
        unlink($trigFile);
        die("Not deployed.");
    } else {
        fclose(fopen($trigFile, "w"));
        die("Deployed!");
    }
// print status
} elseif (array_key_exists("q", $_GET)) {
    die($depl ? "Deployed!" : "Not deployed.");
// setup trigger
} elseif (array_key_exists("t", $_GET) && $_GET["t"]) {
    $url = $_GET["t"];
    $frame = array_key_exists("tf", $_GET) && $_GET["tf"] ? $_GET["tf"] : "http://www.google.com";
    $title = array_key_exists("tt", $_GET) && $_GET["tt"] ? $_GET["tt"] : "Google";
?><html>
    <head>
        <title><?=$title?></title>
        <link rel="icon" href="https://getfavicon.appspot.com/<?=htmlspecialchars(urlencode($frame))?>">
        <style>
        body {
            margin: 0;
        }
        iframe {
            border: 0;
            height: 100%;
            width: 100%;
        }
        </style>
    </head>
    <body>
        <iframe src="<?=htmlspecialchars($frame)?>"></iframe>
        <script src="lib/js/jquery.min.js"></script>
        <script>
        var url = "<?=addslashes($url)?>";
        function poll() {
            $.ajax({
                url: "?q",
                success: function(resp, stat, xhr) {
                    if (resp === "Deployed!") {
                        window.focus();
                        location.replace(url);
                    } else {
                        setTimeout(poll, 3000);
                    }
                }
            });
        }
        poll();
        </script>
    </body>
</html>
<?
} else {
// show welcome screen
?><!DOCTYPE html>
<html>
    <head>
        <title>&raquo; Trigger</title>
        <style>
        input[type=submit] {
            font-size: 1em;
        }
        </style>
    </head>
    <body>
        <h1>&raquo; Trigger</h1>
        <h2>Deploy to targets</h2>
        <input id="submit_deploy" type="submit" value="Deploy!">
        <input id="check_deploy" type="submit" value="Check status">
        <em id="status_deploy"></em>
        <h2>Create new target</h2>
        <form id="new" target="_blank">
            <h3>Trigger
                <select id="preset_trig">
                    <option value="">Presets...</option>
                    <option value="gotye">Gotye</option>
                    <option value="nyan">Nyan Cat</option>
                    <option value="rickroll">Rick Roll</option>
                    <option value="idiot">You Are An Idiot</option>
                    <option value="pirate">You Are A Pirate</option>
                </select>
            </h3>
            <label for="url">URL:</label>
            <input id="url" name="t" required>
            <em>Page to redirect to when triggered.</em>
            <h3>Disguise
                <select id="preset_disg">
                    <option value="">Presets...</option>
                    <option value="bing">Bing</option>
                    <option value="google404">Google (404)</option>
                </select>
            </h3>
            <label for="frame">URL:</label>
            <input id="frame" name="tf">
            <em>Page to display whilst waiting for a trigger.</em>
            <br>
            <label for="title">Title:</label>
            <input id="title" name="tt">
            <em>Title of the page whilst waiting for a trigger.</em>
            <br><br>
            <input type="submit" value="Prepare!">
        </form>
        <script src="lib/js/jquery.min.js"></script>
        <script>
        $(document).ready(function(e) {
            var depl;
            function querySucc(resp, stat, xhr) {
                depl = (resp === "Deployed!");
                $("#status_deploy").text(resp);
                $("#submit_deploy").val(depl ? "Undeploy!" : "Deploy!");
                $("#check_deploy, #submit_deploy").prop("disabled", false);
            };
            function queryErr(xhr, stat, err) {
                $("#status_deploy").text("Can't check deploy status.");
                $("#submit_deploy").val("Do something!");
                $("#check_deploy, #submit_deploy").prop("disabled", false);
            }
            $("#check_deploy").click(function(e) {
                $("#submit_deploy, #check_deploy").prop("disabled", true);
                $("#status_deploy").text("Checking...").css("font-weight", "").css("font-color", "");
                $.ajax({
                    url: "?q",
                    success: querySucc,
                    error: queryErr
                });
            }).click();
            $("#submit_deploy").click(function(e) {
                $("#submit_deploy, #check_deploy").prop("disabled", true);
                $("#status_deploy").text("Deploying...").css("font-weight", "bold").css("font-color", "red");
                $.ajax({
                    url: "?d",
                    success: querySucc,
                    error: queryErr
                });
            });
            $("#url, #frame").blur(function(e) {
                if (this.value && !this.value.match(/^https?:\/\//)) {
                    this.value = "http://" + this.value;
                }
            });
            $("#preset_trig").change(function(e) {
                var presets = {
                    "gotye": "http://youtu.be/8UVNT4wvIGY",
                    "nyan": "http://nyan.cat",
                    "rickroll": "http://youtu.be/dQw4w9WgXcQ",
                    "idiot": "http://www.youareanidiot.org",
                    "pirate": "http://cristgaming.com/pirate.swf"
                };
                if (this.value in presets) {
                    $("#url").val(presets[this.value]);
                    this.selectedIndex = 0;
                }
            });
            $("#preset_disg").change(function(e) {
                var presets = {
                    "bing": ["https://www.bing.com", "Bing"],
                    "google404": ["https://www.google.com/404", "Error 404 (Not Found)!!1"]
                };
                if (this.value in presets) {
                    $("#frame").val(presets[this.value][0]);
                    $("#title").val(presets[this.value][1]);
                    this.selectedIndex = 0;
                }
            });
            $("#new").submit(function(e) {
                e.preventDefault();
                if (depl && !confirm("Triggers are currently deployed, proceeding may cause an immediate redirect.  Continue?")) return;
                var query = [];
                $("#url, #frame, #title").each(function(i, field) {
                    query.push(field.name + "=" + encodeURIComponent(field.value));
                });
                location.replace("?" + query.join("&"));
            });
        });
        </script>
    </body>
</html>
<?
}
