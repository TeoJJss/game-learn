<style>
    .alert-warning {
        background-color: #ff9800;
        color: white;
        position: fixed;
        width: 80vw;
        min-width: 10vw;
        min-height: 6vh;
        font-size: 2vw;
        text-align: center;
        /* padding: 10vh; */
        vertical-align: middle;
        border-radius: 3%;
        font-family: Verdana, Geneva, Tahoma, sans-serif;
        margin-left: 8vw;
    }

    .closebtn {
        margin-left: 15px;
        color: white;
        font-weight: bold;
        float: right;
        font-size: 2vw;
        line-height: 20px;
        cursor: pointer;
        transition: 0.3s;
        margin-top: 2vh;
        margin-right: 1vw;
    }

    .closebtn:hover {
        color: black;
    }

    #warningPopup {
        display: none;
    }
</style>

<div class="alert-warning" id="warningPopup">
    <span class="closebtn">&times;</span>
    <strong>Warning!</strong>the changes are not saved yet
</div>

<script>
    var close = document.getElementsByClassName("closebtn");
    var i;

    for (i = 0; i < close.length; i++) {
        close[i].onclick = function() {
            var div = this.parentElement;
            div.style.opacity = "0";
            setTimeout(function() {
                div.style.display = "none";
            }, 600);
        }
    }
</script>