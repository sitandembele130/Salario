document.addEventListener('DOMContentLoaded', () => {
  // Vérification du salaire avant l'envoi d'un formulaire
  document.querySelectorAll('.salary-form').forEach((form) => {
    const field = form.elements.salary_amount;
    if (!field) return;

    form.addEventListener('submit', (event) => {
      const amount = Number(field.value);
      if (!Number.isFinite(amount) || amount <= 0) {
        event.preventDefault();
        alert('Veuillez saisir un salaire mensuel net supérieur à zéro.');
        field.focus();
      }
    });
  });

  // Bouton « Copier » du jeton secret
  document.querySelectorAll('[data-copy]').forEach((button) => {
    button.addEventListener('click', async () => {
      const source = document.querySelector(button.dataset.copy);
      if (!source) return;
      try {
        await navigator.clipboard.writeText(source.textContent.trim());
        button.textContent = 'Copié ✓';
      } catch (error) {
        window.getSelection().selectAllChildren(source);
        button.textContent = 'Sélectionné : Ctrl+C';
      }
    });
  });
});
