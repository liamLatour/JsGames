<?php
require_once("mdp.php");

try
{
    $bdd = new PDO('mysql:host=localhost;dbname=JsGames;charset=utf8', 'root', $password);
}
catch(Exception $e)
{
        die('Erreur : '.$e->getMessage());
}

if(isset($_POST['user']) && isset($_POST['score'])){
    $req = $bdd->prepare('INSERT INTO Bert(username, score) VALUES(:username, :score)');
    $req->execute(array(
        'username' => $_POST['user'],
        'score' => $_POST['score']
        ));
}
?>
<html>
<head>
    <meta charset="utf-8" />
    <title>B's Ascent</title>
    <link rel="stylesheet" type="text/css" href="menu.css" media="screen"/>
    <style>
    * {
        padding: 0;
        margin: 0;
    }
    canvas {
        border:1px solid #d3d3d3;
        background-color: #f1f1f1;
    }
    html, body {
        width:  100%;
        height: 100%;
        margin: 0px;
    }
    </style>
</head>
<body>
    <div id="menu">
        <ul>
            <li><a href="index.html">Home</a></li>
            <li><a href="casseBrick.html">Breakout</a></li>
            <li><a href="respectWamen.php">R-W</a></li>
            <li><a id="on" href="berteau.php">B's ascent</a></li>
        </ul>
    </div>

    <div class="next1">
        <canvas id="Game" width="480" height="320"></canvas>
    </div>

    <div class="next2">
        <table>
            <tr>
                <th class="title">Name</th>
                <th class="title">Score</th>
                <th class="title">Date</th>
            </tr>

            <?php
                $score = $bdd->query("SELECT * FROM Bert ORDER BY score DESC LIMIT 0, 10");
                while ($donnees = $score->fetch())
                {
            ?>
                <tr>
                    <td><?php echo $donnees['username'] ?></td>
                    <td><?php echo $donnees['score'] ?></td>
                    <td><?php echo $donnees['date'] ?></td>
                </tr>
            <?php
                }
                $score->closeCursor();
            ?>
        </table>
    </div>

    <!-- The Modal -->
    <div id="myModal" class="modal" style="display: none;">
        <!-- Modal content -->
        <div class="modal-content">
            <div class="modal-header">
                <span class="close">&times;</span>
                <h2>Great score !</h2>
            </div>
            <div class="modal-body">
                <p>Enter your name to save your score</p>
		        <form action="berteau.php" method="post">
                    <input type="text" id="user" name="user" value="Ex: TheBest" onfocus="this.value=''">
                    <input type="text" id="score" name="score" value="1" style="display: none">
                    <input type="submit" value="Submit">
		        </form>
            </div>
            <div class="modal-footer">
                <h3>You can find your score at the right of the game</h3>
            </div>
        </div>
    </div>

    <script>
        var modal = document.getElementById('myModal');
        var span = document.getElementsByClassName("close")[0];
        span.onclick = function() {
            modal.style.display = "none";
        }
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>

    <script>
        var score = 0;
        var canvas = document.getElementById("Game");
        var ctx = canvas.getContext("2d");
        ctx.font = "30px Arial";

        var music = new sound("iniD.flac");
        var finished = false;

        var time = new Date().getTime();
        var delay = 5000;
        var timeLeft = delay;
        
        var curKey = 'A';
        var keys = ['A', 'Z', 'D', 'E', 'S', 'Q'];
        var onkeys = [false, false, false, false, false, false];

        var frame = 0;//0 -> 4
        var jumping = false;
        var py = canvas.height-60;

        var mX = 0;
        var mY = 0;

        document.addEventListener("keydown", keyDownHandler, false);
        document.addEventListener("keyup", keyUpHandler, false);
        canvas.addEventListener("click", on_canvas_click, false);

        function on_canvas_click(ev) {
            mX = ev.clientX - canvas.offsetLeft;
            mY = ev.clientY - canvas.offsetTop;
        }

        function keyDownHandler(e) {
            for(var i=0; i<keys.length; i++){
                if(keys[i].charCodeAt(0) == e.keyCode){
                    onkeys[i] = true;
                }
            }
        }

        function keyUpHandler(e) {
            for(var i=0; i<keys.length; i++){
                if(keys[i].charCodeAt(0) == e.keyCode){
                    onkeys[i] = false;
                }
            }
        }

        function End(){
            ctx.beginPath();
            ctx.rect(0, 0, canvas.width, canvas.height);
            ctx.fillStyle = "rgba(120,120,120,0.5)";
            ctx.fill();
            ctx.closePath();

            ctx.beginPath();
            ctx.fillStyle = "rgb(255, 50, 150)";
            ctx.fill();
            ctx.font = "60px Arial";
            ctx.textAlign="center";
            ctx.fillText("Score: " + score,canvas.width/2,canvas.height/2);
            ctx.closePath();

            ctx.beginPath();
            ctx.rect(canvas.width/2-60, canvas.height-120, 120, 50);
            ctx.fillStyle = "#0095DD";
            ctx.fill();
            ctx.closePath();

            ctx.beginPath();
            ctx.fillStyle = "rgb(0, 0, 0)";
            ctx.fill();
            ctx.font = "30px Arial";
            ctx.textAlign="center";
            ctx.fillText("Restart", canvas.width/2, canvas.height-90);
            ctx.closePath();
        }

        function restart(){
            finished = false;

            score = 0;
            time = new Date().getTime();
            delay = 5000;
            timeLeft = delay;

            curKey = 'A';
            keys = ['A', 'Z', 'D', 'E', 'S', 'Q'];
            onkeys = [false, false, false, false, false, false];
 
            frame = 0;//0 -> 4
            jumping = false;
            py = canvas.height-60;

            mX = 0;
            mY = 0;
        }

        function sound(src) {
            this.sound = document.createElement("audio");
            this.sound.src = src;
            this.sound.setAttribute("preload", "auto");
            this.sound.setAttribute("controls", "none");
            this.sound.style.display = "none";
            document.body.appendChild(this.sound);
            this.play = function(){
                this.sound.play();
            }
            this.stop = function(){
                this.sound.pause();
            }
        }

        function Player(){
            ctx.beginPath();
            ctx.rect(canvas.width/2-10, py, 30, 30);
            ctx.fillStyle = "#0095DD";
            ctx.fill();
            ctx.closePath();

            Jump();
        }

        function Jump(){
            for(var i=0; i<onkeys.length; i++){
                if(onkeys[i]){
                    if(keys[i] == curKey){
                        score++;
                        curKey = keys[Math.floor(Math.random()*keys.length)];
                        jumping = true;
                        delay = Math.max(1000, delay-500);
                        timeLeft = delay;
                        time = new Date().getTime();
                    }
                    else{
                        finished = true;
                        if(score != 0){
                            document.getElementById("score").value = score;
			                modal.style.display = "block";
                        }
                    }
                }
            }
            onkeys = [false, false, false, false, false, false];
        }

        function Back(y){
            ctx.transform(1, 0, 0, 1, canvas.width/2-30, canvas.height+y);

            for(var i=0; i<10; i++){                
                ctx.beginPath();

                ctx.fillStyle = "rgb("+ i*22 + ", 120, 120)";

                ctx.fillRect(0, i*-50, 10, 50);
                ctx.fillRect(60, i*-50, 10, 50);

                ctx.fillRect(0, 20+i*-50, 60, 10);

                ctx.closePath();
            }

            ctx.transform(1, 0, 0, 1, -canvas.width/2+30, -canvas.height-y);
        }

        function map(nb, min, max, nmin, nmax){
            return nb*((nmax-nmin)/(max-min))+(nmin-min);
        }

        function UI(){
            ctx.beginPath();
            ctx.arc(80, 92, 20, 0, 2*Math.PI);
            ctx.fillStyle = "rgb(0, 0, 200)";
            ctx.fill();
            ctx.closePath();

            ctx.beginPath();
            ctx.moveTo(80, 92);
            ctx.arc(80, 92, 20, 0, map(timeLeft, 0, delay, 0, 2*Math.PI));
            ctx.fillStyle = "rgb(0, 0, 100)";
            ctx.fill();

            ctx.fillStyle = "rgb(255, 255, 255)";
            ctx.textAlign="center";
            ctx.fillText(curKey, 80, 100);
            ctx.closePath();
        }

        function start(){
            //music.play();
        }
        start();

        function draw(){
            ctx.clearRect(0, 0, canvas.width, canvas.height);

            if(!finished){
                ctx.fillText("Score: " + score, 80, 30);

                timeLeft = delay+(time-new Date().getTime());
                
                if(jumping){
                    py = canvas.height-60 + ((Math.pow(frame-2, 2)+4)*15-8*15);

                    if(frame >= 2.8){
                        Back(((Math.pow(frame-2, 2)+4)*15-8*15));
                    }
                    else{
                        Back(0);
                    }

                    if(py > canvas.height-60){
                        jumping = false;
                        frame = 0;
                    }
                    else{                    
                        frame += 0.1;
                    }
                }
                else{
                    Back(0);
                }

                UI();
                Player();

                
                if(timeLeft < 0){
                    finished = true;
                    if(score != 0){
 		                modal.style.display = "block";
                        document.getElementById("score").value = score;
                    }
                }
            }
            else{
                End();
                if(mX > canvas.width/2-60 && mX < canvas.width/2+60){
                    if(mY > 290 && mY < 340){
                        restart();
                    }
                }
            }
        }
        setInterval(draw, 10);
    </script>
</body>
</html>
