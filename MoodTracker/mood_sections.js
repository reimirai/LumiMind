/* mood_sections.js */
document.addEventListener('DOMContentLoaded', () => {
    const moodFaces = document.querySelectorAll('.mood-face');
    const noteItemsContainer = document.querySelector('.note-items');
    const addNoteButton = document.querySelector('.add-note');

    moodFaces.forEach(face => {
        face.addEventListener('click', (event) => {
            const mood = event.currentTarget.dataset.mood;
            alert(`You selected the mood: ${mood}`);
            // In a real application, you would send this mood data to a server.
        });
    });

    addNoteButton.addEventListener('click', () => {
        const newNoteText = prompt('Enter a new sticky note:');
        if (newNoteText) {
            const newLabel = document.createElement('label');
            const newCheckbox = document.createElement('input');
            newCheckbox.type = 'checkbox';
            newLabel.appendChild(newCheckbox);
            newLabel.appendChild(document.createTextNode(` ${newNoteText}`));
            noteItemsContainer.appendChild(newLabel);
        }
    });

    // Basic functionality for the sticky note checkboxes
    noteItemsContainer.addEventListener('change', (event) => {
        if (event.target.type === 'checkbox') {
            const label = event.target.parentNode;
            if (event.target.checked) {
                label.classList.add('checked');
            } else {
                label.classList.remove('checked');
            }
        }
    });
});