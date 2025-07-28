<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wheel of Fortune</title>
    <script src="tmi.min.js"></script>
    <script src="ChatBot.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Calibri;
        }

        body {
            height: 100vh;
            display: grid;
            place-items: center;
        }

        #wheelOfFortune {
            display: inline-block;
            position: relative;
            overflow: hidden;
        }

        #wheel {
            display: block;
        }

        #value {
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 1.6rem;
            background: #3AdA2A;
            padding: 5px;
            border-radius: 5px;
        }

        #spin {
            font: 1.5em/0 sans-serif;
            user-select: none;
            cursor: pointer;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #2A22a3;
            position: absolute;
            top: 50%;
            left: 50%;
            width: 30%;
            height: 30%;
            margin: -15%;
            background: #fff;
            color: #fff;
            box-shadow: 0 0 0 8px currentColor, 0 0px 15px 5px rgba(0, 0, 0, 0.6);
            border-radius: 50%;
            transition: 0.8s;
        }

        #spin::after {
            content: '';
            position: absolute;
            top: -17px;
            border: 10px solid transparent;
            border-bottom-color: currentColor;
            border-top: none;
        }
    </style>

</head>

<body>
    <div id="wheelOfFortune">
        <span id="value">Faite tourner la roue !</span>
        <canvas id="wheel" width="300" height="300"></canvas>
        <div id="spin">SPIN</div>
    </div>
   <!-- <script src="js/wheel.js"></script>-->
    <script src="js/wheelClass.js"></script>
    <script>
        const wheelObject = new Wheel('#wheel', '#spin', 'value', 'tmp/wheels.json');
        wheelObject.start();
        var bot = new GameBot("GAMEBOT", ["d4rkh0und"]);
        bot.setIgnore("WizeBot");
        bot.setIgnore("wizebot");
        bot.setIgnore("streamelements");
        bot.setCommand(["!spin"], function (arg, tag, channel) {
           // debugger;
           wheelObject.player = tag.username;
            
           wheelObject.spin(()=>{
                bot.message("/me " + wheelObject.player + " voyons ce que tu gagne");
            })
        });
        bot.openBot();
        
    </script>
</body>

</html>