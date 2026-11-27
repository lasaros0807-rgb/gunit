<section class="calculator" aria-labelledby="calculator-title">
    <h2 id="calculator-title">Beräkna billån</h2>

    <div class="calculator-field">
        <label for="amount-number">Lånebelopp (kr)</label>
        <div class="calculator-controls">
            <input id="amount-range" type="range"
                   min="20000" max="1500000" step="1000" value="300000"
                   aria-label="Lånebelopp">
            <input id="amount-number" type="number"
                   min="20000" max="1500000" step="1000" value="300000" required>
        </div>
    </div>

    <div class="calculator-field">
        <label for="rate-number">Årsränta (%)</label>
        <div class="calculator-controls">
            <input id="rate-range" type="range"
                   min="0" max="20" step="0.1" value="5"
                   aria-label="Årsränta">
            <input id="rate-number" type="number"
                   min="0" max="20" step="0.1" value="5" required>
        </div>
    </div>

    <div class="calculator-field">
        <label for="years-number">Återbetalningstid (år)</label>
        <div class="calculator-controls">
            <input id="years-range" type="range"
                   min="1" max="10" step="1" value="5"
                   aria-label="Återbetalningstid">
            <input id="years-number" type="number"
                   min="1" max="10" step="1" value="5" required>
        </div>
    </div>

    <p>Månadskostnad: <strong id="monthly-payment" aria-live="polite"></strong></p>
    <p>Total räntekostnad: <strong id="total-interest"></strong></p>
</section>
