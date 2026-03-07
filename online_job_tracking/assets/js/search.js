// File: assets/js/search.js
function filterJobs() {
    let input = document.getElementById('jobSearch');
    let filter = input.value.toLowerCase();
    let table = document.querySelector("table");
    let tr = table.getElementsByTagName("tr");

    // Loop through all table rows (excluding the header)
    for (let i = 1; i < tr.length; i++) {
        let titleColumn = tr[i].getElementsByTagName("td")[0];
        let categoryColumn = tr[i].getElementsByTagName("td")[1];
        
        if (titleColumn || categoryColumn) {
            let titleText = titleColumn.textContent || titleColumn.innerText;
            let categoryText = categoryColumn.textContent || categoryColumn.innerText;
            
            // If the text matches the filter, show the row; otherwise, hide it
            if (titleText.toLowerCase().indexOf(filter) > -1 || 
                categoryText.toLowerCase().indexOf(filter) > -1) {
                tr[i].style.display = "";
            } else {
                tr[i].style.display = "none";
            }
        }
    }
}