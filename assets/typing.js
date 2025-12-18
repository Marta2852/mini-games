let words = Array.isArray(window.__WORDS__) ? window.__WORDS__ : [];
const level = window.__LEVEL__;

// ✅ remove empty entries (fixes wrong word count if PHP sent blanks)
words = words.map(w => String(w).trim()).filter(w => w.length > 0);

const promptEl = document.getElementById("prompt");
const inputEl = document.getElementById("input");

const timeEl = document.getElementById("time");
const wpmEl = document.getElementById("wpm");
const accEl = document.getElementById("acc");
const correctEl = document.getElementById("correct");

const saveBtn = document.getElementById("saveBtn");
const nickEl = document.getElementById("nickname");
const msgEl = document.getElementById("msg");
const leaderboardEl = document.getElementById("leaderboard");

let started = false;
let finished = false;
let startTime = 0;
let timerId = null;

let currentIndex = 0;
let correctWords = 0;
let totalTypedWords = 0;

// ✅ one shared clean function for both preview + final compare
function clean(s) {
  return String(s)
    .trim()
    // remove common punctuation and quotes (better for paragraphs)
    .replace(/[.,!?;:"'(){}\[\]<>]/g, "")
    // normalize multiple spaces inside (just in case)
    .replace(/\s+/g, " ");
}

function renderPrompt() {
  promptEl.innerHTML = "";
  words.forEach((w, i) => {
    const span = document.createElement("span");
    span.textContent = w + " ";
    span.dataset.index = String(i);
    span.style.padding = "2px 4px";
    span.style.borderRadius = "6px";
    if (i === 0) span.style.outline = "2px solid #999";
    promptEl.appendChild(span);
  });
}
renderPrompt();

scrollActiveIntoView();
function scrollActiveIntoView() {
  const box = document.getElementById("promptBox");
  const spans = promptEl.querySelectorAll("span");
  const el = spans[currentIndex];
  if (!box || !el) return;

  // scroll so active word is visible inside the box
  const boxRect = box.getBoundingClientRect();
  const elRect = el.getBoundingClientRect();

  if (elRect.top < boxRect.top + 10) {
    box.scrollTop -= (boxRect.top + 10 - elRect.top);
  } else if (elRect.bottom > boxRect.bottom - 10) {
    box.scrollTop += (elRect.bottom - (boxRect.bottom - 10));
  }
}


function formatTime(sec) {
  const m = String(Math.floor(sec / 60)).padStart(2, "0");
  const s = String(sec % 60).padStart(2, "0");
  return `${m}:${s}`;
}

function startTimer() {
  started = true;
  startTime = Date.now();
  timerId = setInterval(() => {
    const elapsed = Math.floor((Date.now() - startTime) / 1000);
    timeEl.textContent = formatTime(elapsed);
    updateStats();
  }, 250);
}

function updateStats() {
  const elapsedSec = Math.max(1, Math.floor((Date.now() - startTime) / 1000));
  const minutes = elapsedSec / 60;

  const wpm = Math.round(correctWords / minutes);
  wpmEl.textContent = Number.isFinite(wpm) ? wpm : 0;

  const accuracy =
    totalTypedWords === 0 ? 100 : Math.round((correctWords / totalTypedWords) * 100);

  accEl.textContent = String(accuracy);
  correctEl.textContent = String(correctWords);
}

function setActive(index) {
  const spans = promptEl.querySelectorAll("span");
  spans.forEach(s => (s.style.outline = "none"));
  if (spans[index]) spans[index].style.outline = "2px solid #999";
}

function mark(index, ok) {
  const spans = promptEl.querySelectorAll("span");
  const el = spans[index];
  if (!el) return;
  el.style.outline = "none";
  el.style.background = ok ? "#b9fbc0" : "#ffadad"; // temp
}

function preview(index, ok, hasInput) {
  const spans = promptEl.querySelectorAll("span");
  const el = spans[index];
  if (!el) return;

  // reset preview outline when no input
  if (!hasInput) {
    el.style.outline = index === currentIndex ? "2px solid #999" : "none";
    return;
  }

  el.style.outline = ok ? "2px solid #2a9d8f" : "2px solid #e63946";
}

function finish() {
  finished = true;
  clearInterval(timerId);
  inputEl.disabled = true;
  saveBtn.disabled = false;
  msgEl.textContent = "Finished! Enter nickname and save.";
  updateStats();
}

inputEl.addEventListener("keydown", (e) => {
  if (!started && (e.key.length === 1 || e.key === "Backspace")) startTimer();
});

inputEl.addEventListener("input", () => {
  if (finished) return;

  const currentTyped = inputEl.value;

  const expectedRaw = words[currentIndex] || "";
  const expected = clean(expectedRaw);
  const typedPreview = clean(currentTyped);

  // ✅ realtime feedback using cleaned text
  const okSoFar = expected.startsWith(typedPreview);
  preview(currentIndex, okSoFar, typedPreview.length > 0);

  // ✅ when user presses space, evaluate that word
  if (currentTyped.includes(" ")) {
    const typedWordRaw = currentTyped.trim();
    inputEl.value = "";

    const typedWord = clean(typedWordRaw);
    const ok = typedWord === expected;

    totalTypedWords++;
    if (ok) correctWords++;

    mark(currentIndex, ok);
    currentIndex++;
    setActive(currentIndex);
    scrollActiveIntoView();
    updateStats();

    if (currentIndex >= words.length) finish();
  }
});

async function loadLeaderboard() {
  const res = await fetch("typing/save_score.php?mode=list&level=" + encodeURIComponent(level));
  const data = await res.json();

  if (!data.ok) {
    leaderboardEl.textContent = "Leaderboard error.";
    return;
  }
  if (data.items.length === 0) {
    leaderboardEl.textContent = "No scores yet.";
    return;
  }

  leaderboardEl.innerHTML =
    "<ol>" +
    data.items
      .map(
        (x) =>
          `<li><strong>${escapeHtml(x.nickname)}</strong> — ${x.wpm} WPM, ${x.accuracy}%, time ${escapeHtml(
            x.time
          )}</li>`
      )
      .join("") +
    "</ol>";
}

function escapeHtml(s) {
  return String(s).replace(/[&<>"']/g, (c) => ({
    "&": "&amp;",
    "<": "&lt;",
    ">": "&gt;",
    '"': "&quot;",
    "'": "&#039;",
  }[c]));
}

saveBtn.addEventListener("click", async () => {
  const nickname = nickEl.value.trim();
  if (!nickname) {
    msgEl.textContent = "Enter a nickname.";
    return;
  }

  const elapsedSec = Math.max(1, Math.floor((Date.now() - startTime) / 1000));

  const payload = {
    nickname,
    level,
    wpm: Number(wpmEl.textContent),
    accuracy: Number(accEl.textContent),
    time: timeEl.textContent,
    seconds: elapsedSec,
  };

  const res = await fetch("typing/save_score.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(payload),
  });

  const data = await res.json();
  msgEl.textContent = data.ok ? "Saved!" : (data.error || "Save error");
  if (data.ok) loadLeaderboard();
});

loadLeaderboard();
