function postFormDisplay(status, action) {
    var form = document.getElementById("input-post");
    var createLink = document.getElementById("create-thread");
    if(form.style.display === "inline" || action==='hide') {
        form.style.display = "none";
        if(status === "board")
            createLink.textContent="Создать тред";
        else
            createLink.textContent="Ответить в тред";
    } else {
        form.style.display = "inline";
        createLink.textContent="Закрыть форму постинга";
    }
}

function updater() {
    var checkBox = document.getElementById("autoupdate");
    var timer = document.getElementById("timer");
    if(checkBox.checked == true){
        document.cookie = "updater=true; max-age=2592000";
        var time = timer.innerHTML;
        let timerId = setInterval(function() {
          if(checkBox.checked == false) {
              clearInterval(timerId);
              document.cookie = "updater=false; max-age=2592000";
          }
          timer.innerHTML = time;
          if (time == 0) {
              document.location.reload(true);
          }
          time--;
        }, 1000);
    }
}