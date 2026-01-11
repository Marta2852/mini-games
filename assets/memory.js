const grid = document.getElementById("game");
const timeEl = document.getElementById("time");
const saveBtn = document.getElementById("saveBtn");
const msg = document.getElementById("msg");

let first = null;
let second = null;
let lock = false;
let matched = 0;

let timer = null;
let seconds = 0;
let started = false;

let moves = 0;
let pairsFound = 0;
let totalPairs = window.__PAIRS__;
let currentLevel = window.__LEVEL__;

const levels = {
  "easy": [2, 2],
  "medium": [3, 4],
  "hard": [4, 5],
};

function updateAccuracy() {
    const acc = moves === 0 ? 100 : Math.round((pairsFound / moves) * 100);
    document.getElementById("acc").textContent = acc;
    document.getElementById("moves").textContent = moves;
}

function startTimer() {
    timer = setInterval(() => {
        seconds++;
        const m = String(Math.floor(seconds / 60)).padStart(2, "0");
        const s = String(seconds % 60).padStart(2, "0");
        timeEl.textContent = `${m}:${s}`;
    }, 1000);
}

function shuffle(arr) {
    for (let i = arr.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [arr[i], arr[j]] = [arr[j], arr[i]];
    }
}

function createBoard() {
    grid.innerHTML = '';
    const images = [];
    for (let i = 1; i <= totalPairs; i++) {
        images.push(i, i);
    }

    shuffle(images);

    images.forEach(id => {
        const card = document.createElement("div");
        card.className = "card";
        card.dataset.id = id;

        card.innerHTML = `
            <div class="card-inner">
                <div class="card-front"></div>
                <div class="card-back">
                    <img src="assets/memory/img${id}.png">
                </div>
            </div>
        `;

        card.addEventListener("click", () => flip(card));
        grid.appendChild(card);
    });
}

function resetGame() {
    clearInterval(timer);
    first = null;
    second = null;
    lock = false;
    matched = 0;
    moves = 0;
    pairsFound = 0;
    seconds = 0;
    started = false;
    timeEl.textContent = "00:00";
    updateAccuracy();
    saveBtn.disabled = true;
    msg.textContent = "";
    createBoard();
}

function changeLevel(newLevel) {
    if (!levels[newLevel]) return;
    currentLevel = newLevel;
    const [rows, cols] = levels[newLevel];
    totalPairs = rows * cols / 2;
    grid.style.setProperty('--cols', cols);
    resetGame();
}

// Attach to select
document.querySelector('select[name="level"]').addEventListener('change', (e) => {
    changeLevel(e.target.value);
});

function flip(card) {
    if (lock || card === first || card.classList.contains("matched")) return;

    if (!started) {
        started = true;
        startTimer();
    }

    card.classList.add("flipped");

    if (!first) {
        first = card;
        return;
    }

    second = card;
    lock = true;
    moves++;
    updateAccuracy();

    if (first.dataset.id === second.dataset.id) {
        // Correct match
        pairsFound++;
        updateAccuracy();

        first.classList.add("matched");
        second.classList.add("matched");
        matched += 2;

        resetTurn();

        if (matched === totalPairs * 2) {
            clearInterval(timer);
            saveBtn.disabled = false;
        }
    } else {
        // Wrong match
        setTimeout(() => {
            first.classList.remove("flipped");
            second.classList.remove("flipped");
            resetTurn();
        }, 600);
    }
}

function resetTurn() {
    [first, second] = [null, null];
    lock = false;
}

saveBtn.onclick = () => {
    const nick = document.getElementById("nickname").value.trim();
    if (!nick) return;

    const accuracy = moves === 0 ? 100 : Math.round((pairsFound / moves) * 100);

    fetch("memory/save_score.php", {
        method: "POST",
        headers: {"Content-Type": "application/x-www-form-urlencoded"},
        body: new URLSearchParams({
            nickname: nick,
            time: seconds,
            moves,
            accuracy,
            level: currentLevel
        })
    })
    .then(r => {
        if (!r.ok) throw new Error('Network response was not ok');
        return r.json();
    })
    .then(res => {
        msg.textContent = res.success ? "Saved!" : "Error saving";
        saveBtn.disabled = true;
        window.location.reload();
    })
    .catch(error => {
        console.error('Save failed:', error);
        msg.textContent = "Error saving";
        window.location.reload();
    });
};

// Attach to select
document.querySelector('select[name="level"]').addEventListener('change', (e) => {
    changeLevel(e.target.value);
});

// Initialize
document.querySelector('select[name="level"]').value = "easy";
changeLevel("easy");
