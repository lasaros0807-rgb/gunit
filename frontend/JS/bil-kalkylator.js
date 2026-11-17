const formatter = new Intl.NumberFormat("sv-SE", {
    style: "currency",
    currency: "SEK",
    maximumFractionDigits: 0
});

const fields = [
    { range: document.querySelector("#amount-range"), number: document.querySelector("#amount-number") },
    { range: document.querySelector("#rate-range"), number: document.querySelector("#rate-number") },
    { range: document.querySelector("#years-range"), number: document.querySelector("#years-number") }
];

function updateCalculator() {
    const valid = fields.every(({ number }) => number.value !== "" && number.validity.valid);
    const monthlyPayment = document.querySelector("#monthly-payment");
    const totalInterest = document.querySelector("#total-interest");

    if (!valid) {
        monthlyPayment.textContent = "Kontrollera värdena";
        totalInterest.textContent = "—";
        return;
    }

    const amount = Number(fields[0].number.value);
    const annualRate = Number(fields[1].number.value);
    const months = Number(fields[2].number.value) * 12;
    const monthlyRate = annualRate / 100 / 12;

    const payment = monthlyRate === 0
        ? amount / months
        : amount * monthlyRate / (1 - Math.pow(1 + monthlyRate, -months));

    monthlyPayment.textContent = formatter.format(payment);
    totalInterest.textContent = formatter.format(payment * months - amount);
}

for (const { range, number } of fields) {
    range.addEventListener("input", () => {
        number.value = range.value;
        updateCalculator();
    });

    number.addEventListener("input", () => {
        if (number.value !== "" && number.validity.valid) {
            range.value = number.value;
        }
        updateCalculator();
    });
}

updateCalculator();
