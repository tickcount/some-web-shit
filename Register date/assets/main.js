var xmlhttp = new XMLHttpRequest();
xmlhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
        searchBlock.classList.remove('search_loading');
        $('#searchUserResult').html(this.responseText);
    }
};

function searchUser(id) {
    if (id.length == 0) {
        $('#searchUserResult').html('');
        return;
    } else {
        searchBlock.classList.add('search_loading');
        xmlhttp.open('GET', 'search.php?user=' + id, true);
        xmlhttp.send();
    }
}

function searchReset() {
    $('#searchUserResult').html('');
    searchForm.reset();
}