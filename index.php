<?php
session_start();
require_once "twitchFnc.php";
$liveInformations = json_decode(file_get_contents("tmp/live.json"), true);
$gameInformations = json_decode(file_get_contents("tmp/game.json"), true);
$client_id = '2ryqf8otdnubrdc53vq8uwyxamvns7';
$redirect_uri = 'https://assets.schoolweb.fr/bot/tmp/callback.php';
$scope = 'channel:manage:broadcast+analytics:read:games';
$settitle = "";
$settags = "";
$chaine = "d4rkh0und";
if (isset($_GET["delMessage"])) {
    $data = $liveInformations[$chaine]["messages"];
    unset($data[$_GET["delMessage"]]);
    $liveInformations[$chaine]["messages"] = $data;
    $str = json_encode($liveInformations);
    file_put_contents("tmp/live.json", $str);
    echo "Les informations on été mise à jour";
    echo "<script>setTimeout(function () {
                          window.location.href =\"index.php\"},3000)</script>";
}
if (isset($_GET["addGame"])) {
    $data = $gameInformations[$chaine];
    $data[$_POST["idTitle"]]["name"] = $_POST["gameTitle"];
    $data[$_POST["idTitle"]]["editeur"] = $_POST["gameEditor"];
    $data[$_POST["idTitle"]]["sortie"] = $_POST["gameYear"];
    $gameInformations[$chaine] = $data;
    $strGameInfo = json_encode($gameInformations);
    file_put_contents("tmp/game.json", $strGameInfo);
    echo "Les informations on été mise à jour";
    echo "<script>setTimeout(function () {
                          window.location.href =\"index.php\"},3000)</script>";
}
if (isset($_GET["updateLive"])) {
    if (!isset($liveInformations[$chaine]["data"][$_GET["updateLive"]])) {
        echo "Il n'y a pas d'informations pour ce live ID:" . $_GET["delLive"];
        exit();
    }
    $settitle = $liveInformations[$chaine]["data"][$_GET["updateLive"]]["title"];
    $settags = implode(",", $liveInformations[$chaine]["data"][$_GET["updateLive"]]["tags"]);
}
if (isset($_GET["ValidUpdate"])) {
    if (!isset($liveInformations[$chaine]["data"][$_GET["ValidUpdate"]])) {
        echo "Il n'y a pas d'informations pour ce live ID:" . $_GET["ValidUpdate"];
        exit();
    }
    $data = $liveInformations[$chaine]["data"][$_GET["ValidUpdate"]];
    $data["title"] = $_POST["title"];
    $data["tags"] = explode(",", $_POST["tags"]);
    if ($_POST["game"] != "null") {
        $data["game_id"] = $_POST["game"];
    }
    $liveInformations[$chaine]["data"][$_GET["ValidUpdate"]] = $data;
    $jsonStr = json_encode($liveInformations);
    file_put_contents("tmp/live.json", $jsonStr);
    echo "Les informations on été mise à jour";
    echo "<script>setTimeout(function () {
                          window.location.href =\"index.php\"},3000)</script>";

}
if (isset($_GET["delLive"])) {
    if (!isset($liveInformations[$chaine]["data"][$_GET["delLive"]])) {
        echo "Il n'y a pas d'informations pour ce live ID:" . $_GET["delLive"];
        exit();
    }
    unset($liveInformations[$chaine]["data"][$_GET["delLive"]]);
    $jsonStr = json_encode($liveInformations);
    file_put_contents("tmp/live.json", $jsonStr);
    echo "Les informations on été mise à jour";
    echo "<script>setTimeout(function () {
                        window.location.href =\"index.php\"},3000)</script>";
}
if ($_SERVER['HTTP_HOST'] != 'localhost' && $_SERVER['HTTP_HOST'] != '127.0.0.1') {
    $local = false;
    if (isset($_GET["deconnect"])) {
        session_destroy();
        echo "Vous allez être deconnecter";
        echo "<script>setTimeout(function(){window.location.href=\"index.php\"},3000)</script>";

    }

    if (isset($_SESSION['access_token'])) {

        $access_token = $_SESSION['access_token'];
        $games = getGames($access_token, $client_id)["data"];
        //echo "$access_token<br>";
        $user_data = getUser($access_token, $client_id);
        $_SESSION["broadcaster_id"] = $user_data["data"][0]["id"];
        $chaine = $user_data["data"][0]["login"];
        $profileImg = $user_data["data"][0]["profile_image_url"];
        echo "<a class='twitch_connexion' href='index.php?deconnect='>Se déconnecter</a>";
        // echo "https://api.twitch.tv/helix/channels?broadcaster_id=".$_SESSION["broadcaster_id"];
        if (isset($_GET["addLive"])) {
            $data = $liveInformations[$chaine]["data"];
            $i = count($data);
            $data[$i]["title"] = $_POST["title"];
            $tagsArray = explode(",", $_POST["tags"]);
            var_dump($tagsArray);
            $data[$i]["tags"] = $tagsArray;
            if ($_POST["game"] != "") {
                $data[$i]["game_id"] = $_POST["game"];
            }
            $liveInformations[$chaine]["data"] = $data;
            //  var_dump($liveInformations);//Première vérification
            $jsonStr = json_encode($liveInformations);
            file_put_contents("tmp/live.json", $jsonStr);
            echo "Les informations on été mise à jour";
            echo "<script>setTimeout(function () {
        window.location.href =\"index.php\"},3000)</script>";
        }


        if (isset($_GET["setLive"])) {
            if (!isset($liveInformations[$chaine]["data"][$_GET["setLive"]])) {
                echo "Il n'y a pas d'informations pour ce live ID:" . $_GET["setLive"];
                var_dump($liveInformations);
                exit();
            }
            $data = $liveInformations[$chaine]["data"][$_GET["setLive"]];

            $tags = implode(",", $data["tags"]);
            //$data["tags"]="[$tags]";
            // var_dump($data);
            // Initialiser la session cURL
            $ch = curl_init();

            // Configurer les options de la requête
            curl_setopt($ch, CURLOPT_URL, "https://api.twitch.tv/helix/channels?broadcaster_id=" . $_SESSION["broadcaster_id"]);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            // Ajouter les en-têtes
            $headers = [
                "Authorization: Bearer $access_token",
                "Client-Id: $client_id",
                "Content-Type: application/json",
            ];
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            // Ajouter les données JSON
            /* $data = [
                 "title"=>"Morning dev'",
                 "tags" => ["Chill","StreamFrancais","JavaScript","CSS","PHP","HTML","DevWeb","widget","Français"]
             ];*/
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

            // Exécuter la requête et obtenir la réponse
            $response = curl_exec($ch);

            // Gérer les erreurs
            if (curl_errno($ch)) {
                echo 'Erreur cURL : ' . curl_error($ch);
            } else {
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                if ($httpCode == "204") {
                    echo "Les informations on été mise à jour";
                    echo "<script>setTimeout(function () {
                        window.location.href =\"index.php\"},3000)</script>";
                } else {
                    echo $httpCode;
                }

            }

            // Fermer la session cURL
            curl_close($ch);

        }
    }
}
if (isset($_GET["addMessage"])) {
    if (!isset($_POST["messageContent"])) {
        echo "pas de message posté !";
        echo "<script>setTimeout(function () {
        window.location.href =\"index.php\"},3000)</script>";
    }
    if (empty($_POST["messageContent"])) {
        echo "pas de message posté !";
        echo "<script>setTimeout(function () {
        window.location.href =\"index.php\"},3000)</script>";
    }
    $liveInformations[$chaine]["messages"][] = $_POST["messageContent"];
    ;
    $str = json_encode($liveInformations);
    file_put_contents("./tmp/live.json", $str);
    echo "Information mise à jour !";
    echo "<script>setTimeout(function () {
        window.location.href =\"index.php\"},5000)</script>";

}
if (!isset($_SESSION['access_token'])) {
    $auth_url = "https://id.twitch.tv/oauth2/authorize?response_type=code&client_id=$client_id&redirect_uri=$redirect_uri&scope=$scope";
    echo "<a class='twitch_connexion' href='$auth_url'>Se connecter avec Twitch</a>";
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="tmi.min.js"></script>
    <script src="ChatBot.js"></script>
    <script src="js/botCmd.js"></script>
    <script src="js/core.js"></script>
    <script src="js/voice.js"></script>
    <script src="twitchRequest.js"></script>
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <title>MyGameBot</title>
    <link rel="stylesheet" href="_css/index.css">
</head>

<body>
    <div class="container">
        <div class="games">
            <div>
                <h3>Jeux vidéo</h3>
                <a href="#" data-close=".games">X</a>
            </div>
            <ul>
                <?php
                $data = $gameInformations[$chaine];
                foreach ($data as $key => $value) {
                    $name = (strlen($value["name"]) > 20) ? substr($value["name"], 0, length: 20) . "..." : $value["name"];
                    echo "<li>[<strong>$key</strong>] $name</li>";
                }
                ?>
            </ul>
        </div>
        <div class="messages">
            <div>
                <h3>Messages</h3>
                <a href="#" data-close=".messages">X</a>
            </div>
            <?php
            $data = $liveInformations[$chaine]["messages"];
            $i = 0;
            foreach ($data as $message) {
                echo "<div>
                        <p>$message</p>
                        <a href='?delMessage=$i'>
                        <i class=\"fa-solid fa-trash\"></i>
                        </a>
                    </div>";
                $i++;
            }
            ?>
        </div>
        <h1>MyGameBot - Beta</h1>
        <div class="listCmd"></div>
        <div class="listLive">
            <?php
            $lives = $liveInformations[$chaine]["data"];
            $i = 0;
            foreach ($lives as $l) {
                ?>
                <section>
                    <?php
                    if (isset($games)) {
                        if (isset($l["game_id"])) {
                            $gameInfo = array_filter($games, function ($g) use ($l) {
                                return ($g["id"] === $l["game_id"]);
                            });
                            $url = current($gameInfo)["box_art_url"];
                            $url = str_replace("{width}", "50", $url);
                            $url = str_replace("{height}", "50", $url);
                            echo "<img src='$url'>";
                        }
                    }
                    ?>
                    <div class="links">
                        <a href="index.php?setLive=<?php echo $i; ?>">
                            <?php echo $l["title"]; ?>
                        </a>
                        <a href="index.php?delLive=<?php echo $i; ?>"><i class="fa-solid fa-trash"></i></a>
                        <a href="index.php?updateLive=<?php echo $i; ?>"><i class="fa-solid fa-pen"></i></a>


                        <ul>
                            <?php foreach ($l["tags"] as $tag) {
                                echo "<li>$tag</li>";
                            } ?>
                        </ul>
                    </div>
                </section>
                <?php
                $i++;
            }
            ?>
            <section id="forms">
                <div class="ctrl">
                    <button><i class="fa-solid fa-angles-left"></i></button>
                    <button><i class="fa-solid fa-angles-right"></i></button>
                    <button name="sayHello"><i class="fa-solid fa-volume-high"></i></button>
                    <a href="#" name="getGames" title="voir les jeux disponibles"><i
                            class="fa-regular fa-rectangle-list"></i></a>
                    <a href="#" name="getMessages" title="voir les Messages"><i
                            class="fa-regular fa-rectangle-list"></i></a>
                </div>

                <div class="form">
                    <form action="index.php?addGame" method="post">
                        <h2>Ajouter un jeu</h2>
                        <div class="compoment">
                            <label for="GameTitle">Nom du jeu : </label>
                            <input type="text" id="GameTitle" name="gameTitle">
                            <input type="text" name="idTitle">
                        </div>
                        <div class="compoment">
                            <label for="GameEditor">Editeur : </label>
                            <input type="text" id="GameEditor" name="gameEditor">
                        </div>
                        <div class="compoment">
                            <label for="GameYear">Année de sortie : </label>
                            <input type="text" id="GameYear" name="gameYear">
                        </div>
                        <div class="compoment">
                            <button type="submit">Ajouter</button>
                        </div>
                    </form>
                </div>
                <?php
                $direct = isset($_GET["updateLive"]) ? "ValidUpdate=" . $_GET["updateLive"] : "addLive=";
                ?>
                <div class="form">
                    <form action="index.php?<?php echo $direct ?>" method="POST">
                        <?php
                        if (!isset($_GET["updateLive"])) {
                            echo "<h2>Paramètrer un Live</h2>";
                        } else {
                            echo "<h2>Modifier le Live</h2>";
                        }
                        ?>
                        <div class="compoment">
                            <label for="LiveName">Nom du live : </label>
                            <input type="text" id="LiveName" name="title" value="<?php echo $settitle; ?>">
                        </div>
                        <div class="compoment">
                            <label for="TagsLive">Tags du live : </label>
                            <input type="text" id="TagsLive" name="tags" value="<?php echo $settags; ?>">
                        </div>
                        <div class="compoment">
                            <label for="gameId">Catégorie</label>
                            <select name="game" id="gameId">
                                <option value="null">Séléctionner une Catégorie</option>
                                <?php
                                // print_r($games);
                                if (isset($game)) {
                                    foreach ($games as $game) {
                                        echo "<option value=" . $game["id"] . ">" . $game["name"] . "</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        <div class="compoment">
                            <button>Ajouter</button>
                        </div>
                    </form>
                </div>
                <div class="form">
                    <form method="POST" action="index.php?addMessage">
                        <h2>Les messages <span></span></h2>
                        <div class="compoment">
                            <label for="messageContent">Message : </label>
                            <textarea name="messageContent" id="messageContent"></textarea>
                        </div>
                        <div class="compoment">
                            <button>Ajouter</button>
                        </div>
                    </form>
                </div>
            </section>
        </div>
        <div class="console"></div>
    </div>
    <script>

        var viewers = [];
        var queue = [];
        var game = undefined;
        const CurrentChannel = "<?php echo $chaine; ?>";
        const LISTS = {
            games: document.querySelector(".games"),
            messages: document.querySelector(".messages")
        };
        document.querySelector("[name=sayHello]").onclick=function(){
            sayHello(viewers);
        }
        document.querySelectorAll("[data-close]").forEach(el => {
            el.onclick = function () {
                let target = document.querySelector(this.getAttribute("data-close"))
                if (target != undefined) {
                    target.style.display = "none";
                } else {
                    console.error("l'élément cible n'existe pas")
                }
                return false;
            }
        })
        Object.entries(LISTS).forEach(el => {
            el[1].style.display = "none";
        })
        document.querySelector("[name=getGames]").onclick = function () {
            LISTS.games.style.display = "block";
            return false;
        }
        document.querySelector("[name=getMessages]").onclick = function () {
            LISTS.messages.style.display = "block";
            return false;
        }
        const forms = document.querySelectorAll(".form");
        var ctrl = document.querySelectorAll(".ctrl button");
        var currentForm = 0;
        ctrl[0].addEventListener("click", function () {
            if (currentForm - 1 >= 0) {
                currentForm--;
            }
            displayForm();
        })
        ctrl[1].addEventListener("click", function () {
            if (currentForm + 1 < forms.length) {
                currentForm++;
            }
            displayForm();
        })
        function displayForm() {
            forms.forEach(el => {
                el.style.display = "none";
            });
            forms[currentForm].style.display = "flex";
        }
        displayForm();
        var twitchQuery = new TwitchRequest("2ryqf8otdnubrdc53vq8uwyxamvns7", "bc49b0dlhjsht4cfi73t5ra62mi201");
        twitchQuery.execQuery("getLastClip",{broadcaster:"d4rkh0und"},function(data){
            console.dir(data[0]);
        })
        twitchQuery.addQuery("getChannelFollowers", async function (param, clientId, token, fnc) {
            twitchQuery.execQuery("getChannelInfo", { broadcaster: "d4rkh0und" }, async function (data) {
                const channelID = data.id;
                const channelInfoUrl = `https://api.twitch.tv/helix/channels/followers?broadcaster_id=${channelID}`;
                const channelInfoRequestOptions = {
                    method: "GET",
                    headers: {
                        "Client-Id": clientId,
                        "Authorization": `Bearer ${token}`
                    }
                };
                try {
                    const channelInfoResponse = await fetch(channelInfoUrl, channelInfoRequestOptions);
                    if (!channelInfoResponse.ok) {
                        throw new Error(`API Error: ${channelInfoResponse.status} - ${channelInfoResponse.statusText}`);
                    }

                    const channelinfoResult = await channelInfoResponse.json();
                    if (channelinfoResult.data && channelinfoResult.data.length > 0) {
                        fnc.call(this, channelinfoResult.data[0]);
                    } else {
                        console.warn("No data found for broadcaster:", param.broadcaster);
                    }
                } catch (error) {
                    console.error("Error fetching channel info:", error);
                }
            });
        });

        getFollowers = function () {
            twitchQuery.execQuery("getChannelFollowers", {}, function (data) {
                console.dir(data);
            }, false, "channel:manage:broadcast");
        }
        setTag = function () {
            twitchQuery.execQuery("setChannelTag", { tags: ["test", "test2"] }, function (data) {
                console.dir(data);
                let tags = data.tags.join(",");
                document.querySelector(".console").innerHTML = `[${tags}]`
            }, false, "channel:manage:broadcast");
        }
        setQueue = function () {
            let container = document.querySelector("#queue>section");
            container.innerHTML = "";
            queue.forEach(el => {
                let span = document.createElement("span");
                span.innerHTML = el;
                span.onclick = function () {
                    //    debugger;
                    const indexElement = queue.indexOf(this.innerHTML);
                    queue.splice(indexElement, 1);
                    bot.writeFile("tmp/queue.json", JSON.stringify(queue));
                    bot.message("/me @" + this.innerHTML + " prépare toi, tu va jouer");
                    this.remove();
                }
                container.appendChild(span);
            });
        }
        var bot = new GameBot("GAMEBOT", ["d4rkh0und"]);
        bot.mInterval = 2;
        bot.setIgnore("WizeBot");
        bot.setIgnore("wizebot");
        bot.setIgnore("streamelements");
        let descCmd={
            "!chaine":"Obtenir les informations de la chaine",
            "!setgameinfo":"Change le jeu pour obtenir ses informations",
            "!infos":"Donne les informations du jeu",
            "!gameinfo":"Donne les informations du jeu",
            "!join":"Permet de s'ajouter à la file d'attente du jeu",
            "!setliveinfo":"Change le projet ou le jeu du footer",
            "!reset":"Efface la file d'attente",
            "!leave":"Permet de se retirer de la file d'attente du jeu",
            "!mydiscord":"lien du discord"
        }
        bot.setCommand("!chaine", botCmd["getChannelInfo"]);
        bot.setCommand("!setGameInfo", botCmd["setGameInfo"]);
        bot.setCommand(["!Infos", "!gameInfo"], botCmd["getGameInfo"]);
        bot.setCommand("!join", botCmd["joinQueue"]);
        bot.setCommand("!setliveinfo", botCmd["setLiveInfo"]);
        bot.setCommand("!setlivename", botCmd["setLiveName"]);
        bot.setCommand("!reset", botCmd["resetQueue"]);
        bot.setCommand("!leave", botCmd["leaveQueue"]);
        bot.setCommand("!mydiscord", botCmd["getDiscord"]);
       // bot.setCommand("!sayHello",botCmd["sayHello"]);
        let lst=document.querySelector('.listCmd');
            lst.innerHTML="";
        let commands=bot.cmd;
        //console.dir(Object.keys(commands));
        Object.keys(commands).forEach(c=>{
            lst.innerHTML+=`<span>${c} => ${descCmd[c]}</span>`;
        })
        bot.openBot();
        bot.setMessageFromFile().then(messages => {
            bot.diffuseMessages();
            
        });
        bot.getBot().on('raided', (channel, username, viewers) => {
            console.log("Raid détecté !");
            bot.message("/me Bienvenue @" + username + " ainsi que ses " + viewers + " viewers, installez-vous et profitez bien du show");
        });
        bot.getBot().on('subgift', (channel, username, streakMonths, recipient, methods, userstate) => {
            bot.message(`/me ${username} a offert un abonnement à ${recipient} gloire à ${username}`);
        });
        bot.getBot().on('subscription', (channel, username, method, message, userstate) => {
            bot.message(`/me ${username} lache son abonnement quelle joie !`);
        });
        bot.getBot().on('join', (channel, username, self) => {
            //      console.dir(channel);
            if (!self) {
                if (!bot.isIgnore(username)) {
                    viewers.push(username);
                    voiceHello.push(username);
                    let s=JSON.stringify(voiceHello);
               let result= bot.writeFile("./tmp/voiceHello.json", s);
                    bot.message("/me Bienvenue @" + username + ", installe toi et profite");
                }
            }
        });
        function sayHelloAll() {
            let welcomeFnc = setInterval(function () {
                if (voiceHello.length == 0) {
                    clearInterval(welcomeFnc);
                }
                console.log("try say Hello for" + voiceHello.length)
                sayHello(voiceHello)
            }, 5000);
        }
    </script>
</body>

</html>