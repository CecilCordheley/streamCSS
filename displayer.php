<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Displayer</title>
    <script src="js/core.js"></script>
    <script src="js/voice.js"></script>
    <script src="tmi.min.js"></script>
    <script src="chatBot.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Calibri;
            font-size: 1.6rem
        }

        #displayer {
            width: 450px;
            height: 100px;
            background: #005A;
            border-radius: 50% / 10%;
            margin: 10px;
            color :#FFF;
        }

        #displayer span.displayName {
            color: #A36;
        }
    </style>
</head>

<body>
    <div id="displayer">

    </div>
    <script>
        let displayer = document.getElementById("displayer");
        function displayHello(username) {
            let displayName = document.createElement("div");
            displayName.innerHTML = "Bienvenue : <span class='displayName'>" + username + "</span> installe toi et profite";
            displayer.appendChild(displayName);
            setTimeout(function () {
                displayer.innerHTML = ""
            }, 5000);
        }
        var bot = new GameBot("GAMEBOT", ["d4rkh0und"]);
        bot.mInterval = 2;
        bot.setIgnore("WizeBot");
        bot.setIgnore("wizebot");
        bot.setIgnore("streamelements");
        bot.openBot();
        var viewers = [];
        bot.getBot().on('join', (channel, username, self) => {
            //      console.dir(channel);
            if (!self) {
                if (!bot.isIgnore(username)) {
              //      bot.message("/me Bienvenue @" + username + ", installe toi et profite");
                    viewers.push(username);
                }
            }
        });
        setInterval(function () {
            console.dir(viewers);
            if (viewers.length > 0)
                displayHello(viewers[0]);
            viewers.shift();
        }, 10000);
    </script>
</body>

</html>