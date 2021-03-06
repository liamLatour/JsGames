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
    $req = $bdd->prepare('INSERT INTO RWscore(username, score) VALUES(:username, :score)');
    $req->execute(array(
        'username' => $_POST['user'],
        'score' => $_POST['score']
        ));
}
?>
<html>
<head>
    <meta charset="utf-8" />
    <title>R-W</title>
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
            <li><a id="on" href="respectWamen.php">R-W</a></li>
            <li><a href="berteau.php">B's ascent</a></li>
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
                $score = $bdd->query("SELECT * FROM RWscore ORDER BY score DESC LIMIT 0, 10");
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
		        <form action="respectWamen.php" method="post">
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
        var canvas = document.getElementById("Game");
        var ctx = canvas.getContext("2d");
        ctx.font = "30px Arial";

        var music = new sound("iniD.flac");
        var finished = false;
        var timer = new Date().getTime() + 15000;
        var score = 0;
        var usedSlots = [false,false,false,false,false,false,false,false,false,false];

        var x = 20;
        var y = canvas.height-30;
        var h = 30;

        var rightPressed = false;
        var leftPressed = false;
        var spacePressed = false;
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
            if(e.keyCode == 39) {
                rightPressed = true;
            }
            else if(e.keyCode == 37) {
                leftPressed = true;
            }
            else if(e.keyCode == 32){
                spacePressed = true;
            }
        }

        function keyUpHandler(e) {
            if(e.keyCode == 39) {
                rightPressed = false;
            }
            else if(e.keyCode == 37) {
                leftPressed = false;
            }
            else if(e.keyCode == 32){
                spacePressed = false;
            }
        }

        function Player(){
            ctx.beginPath();
            ctx.rect(x, y, 30, h);
            ctx.fillStyle = "#0095DD";
            ctx.fill();
            ctx.closePath();
        }

        function Move(){
            if(spacePressed){
                h = 25;
                y = canvas.height-25;

                for(var i=0; i<10; i++){
                    if(x < canvas.width/10*i + 15 && x > canvas.width/10*i && usedSlots[i]){
                        usedSlots[i] = false;
                        score++;
                    }
                }
            }
            else{
                h = 30;
                y = canvas.height-30;
                if(rightPressed){
                    x += 3;
                }
                else if(leftPressed){
                    x -= 3;
                }
            }

            if(x+30 > canvas.width){
                x = canvas.width-30;
            }
            else if(x < 0){
                x = 0;
            }
        }

        function Female(slot){
            ctx.beginPath();
            ctx.rect(canvas.width/10*slot + 10, canvas.height-25, 25, 25);
            ctx.fillStyle = 'rgb(255, 50, 150)';
            ctx.fill();
            ctx.closePath();
        }

        function AddFemale(){
            for(var i=0; i<10; i++){
                if(!usedSlots[i]){
                    if(Math.random() < 0.005){
                        usedSlots[i] = true;
                        break;
                    }
                }
            }
        }

        function ShowFemale(){
            for(var i=0; i<10; i++){
                if(usedSlots[i]){
                    Female(i);
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
            mX = 0;
            mY = 0;
            x = 20;
            y = 290;
            h = 30;
            usedSlots = [false,false,false,false,false,false,false,false,false,false];
            timer = new Date().getTime() + 15000;
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

        function start(){
            music.play();
        }
        start();

        function draw(){
            var time = Math.round((timer-new Date().getTime())/1000);

            if(time > -1){
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                ctx.textAlign="center";
                ctx.fillText("Score: " + score,canvas.width/2,50);
                ctx.textAlign="start";
                ctx.fillText("Time: " + time,0,50);

                Player();

                AddFemale();
                ShowFemale();

                Move();
            }
            else if(!finished){
                End();
                finished = true;
                if(score != 0){
                    document.getElementById("score").value = score;
                    modal.style.display = "block";
                }
            }
            else{
                if(mX > canvas.width/2-60 && mX < canvas.width/2+60){
                    if(mY > 290 && mY < 340){
                        restart();
                    }
                }
            }
        }
        var close = setInterval(draw, 10);
    </script>
</body>
</html>
