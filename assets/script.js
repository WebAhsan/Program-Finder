document.getElementById("pf-toggle-map").addEventListener("click", function () {
document.getElementById("pf-map-container").classList.remove("d-none");
document.getElementById("pf-results-container").classList.add("d-none");
this.classList.add("active");
document.getElementById("pf-toggle-grid").classList.remove("active");
});

document.getElementById("pf-toggle-grid").addEventListener("click", function () {
    document.getElementById("pf-map-container").classList.add("d-none");
    document.getElementById("pf-results-container").classList.remove("d-none");
    this.classList.add("active");
    document.getElementById("pf-toggle-map").classList.remove("active");
});


