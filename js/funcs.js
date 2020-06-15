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
