window.onload = function() {
    // Récupérer les données de la carte et les afficher
    document.getElementById("prenom-display").textContent = localStorage.getItem("prenom");
    document.getElementById("nom-display").textContent = localStorage.getItem("nom");
    document.getElementById("tel-display").textContent = localStorage.getItem("tel");
    document.getElementById("naissance-display").textContent = localStorage.getItem("naissance");
    document.getElementById("region-display").textContent = localStorage.getItem("region");
    document.getElementById("departement-display").textContent = localStorage.getItem("departement");
    document.getElementById("commune-display").textContent = localStorage.getItem("commune");
    document.getElementById("adresse-display").textContent = localStorage.getItem("adresse");
    document.getElementById("cni-display").textContent = localStorage.getItem("cni");
    document.getElementById("electeur-display").textContent = localStorage.getItem("electeur");
    const photo = localStorage.getItem("photo");
    if (photo) {
      document.getElementById("photo-display").style.backgroundImage = `url(${photo})`;
      document.getElementById("photo-display").style.backgroundSize = 'cover';
    }
  };
  
  function generatePDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();
  
    // Convertir la carte en image
    html2canvas(document.querySelector(".carte")).then(canvas => {
      const imgData = canvas.toDataURL('image/png');
      doc.addImage(imgData, 'PNG', 10, 10, 180, 120);
      doc.save('carte_membre.pdf');
    });
  }
  