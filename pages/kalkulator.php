<?php
// pages/kalkulator.php
?>
<div class="container my-5">
  <h1 class="mb-4">Kalkulator Garażu lub Domu</h1>
  <form id="calcForm">
    <div class="mb-3">
      <label for="type" class="form-label">Rodzaj budynku:</label>
      <select id="type" class="form-select" required>
        <option value="garaz">Garaż</option>
        <option value="dom">Dom</option>
      </select>
    </div>
    <div class="mb-3">
      <label for="area" class="form-label">Powierzchnia (m²):</label>
      <input type="number" id="area" class="form-control" required min="1" placeholder="np. 60">
    </div>
    <div class="mb-3">
      <label for="city" class="form-label">Twoja miejscowość:</label>
      <input type="text" id="city" class="form-control" required placeholder="np. Radom">
    </div>
    <button type="submit" class="btn btn-primary">Oblicz koszt</button>
  </form>
  <div class="mt-4">
    <p class="text-muted">* Cena podana przez kalkulator ma charakter poglądowy i może się różnić.</p>
    <div id="result"></div>
  </div>
</div>

<!-- Dodaj poniższy skrypt lub załaduj go z zewnętrznego pliku -->
<script>
document.getElementById("calcForm").addEventListener("submit", function (e) {
  e.preventDefault();
  const area = parseFloat(document.getElementById("area").value);
  const type = document.getElementById("type").value;
  const resultBox = document.getElementById("result");

  let materials = 0, plates = 0;
  const PLATE_AREA = 2.4 * 6;
  const PLATE_COST = 6000;
  const DOM_M2_COST = 834;
  const TRANSPORT_RATE = 10;
  // Przyjmijmy przykładową stałą odległość:
  const distanceKm = 20;

  if (type === 'dom') {
    materials = area * DOM_M2_COST;
  } else {
    plates = Math.ceil(area / PLATE_AREA);
    materials = plates * PLATE_COST;
  }
  
  const transport = distanceKm * 2 * TRANSPORT_RATE;
  const total = Math.round(materials + transport);
  
  resultBox.innerHTML = `
    <h2>Wynik:</h2>
    <p>Typ budowy: <strong>${type === 'dom' ? 'Dom' : 'Garaż'}</strong></p>
    <p>Powierzchnia: <strong>${area} m²</strong></p>
    ${type === 'garaz' ? `<p>Liczba płyt: <strong>${plates}</strong></p>` : ''}
    <p>Przykładowa odległość: <strong>${(distanceKm * 2).toFixed(1)} km</strong></p>
    <p>Koszt materiałów: <strong>${Math.round(materials).toLocaleString()} zł</strong></p>
    <p>Koszt transportu: <strong>${transport.toLocaleString()} zł</strong></p>
    <p><strong>Łączny koszt: ${total.toLocaleString()} zł</strong></p>
  `;
});
</script>
