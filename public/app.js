const commentsElement = document.querySelector('#comments');
const commentForm = document.querySelector('#comment-form');
const commentText = document.querySelector('#comment-text');
const characterCount = document.querySelector('#character-count');
const template = document.querySelector('#comment-template');

commentText.addEventListener('input', () => {
  characterCount.textContent = `${commentText.value.length} / 500`;
});

async function api(action, options = {}) {
  const response = await fetch(`/api.php?action=${encodeURIComponent(action)}`, options);
  const payload = await response.json();
  if (!response.ok) {
    throw new Error(payload.error || 'No fue posible completar la operacion.');
  }
  return payload;
}

async function loadComments() {
  try {
    const payload = await api('list');
    commentsElement.replaceChildren();

    for (const comment of payload.comments) {
      const article = template.content.firstElementChild.cloneNode(true);
      article.dataset.id = String(comment.id);
      article.querySelector('.comment-text').textContent = comment.text;
      article.querySelector('time').textContent = new Date(comment.created_at).toLocaleString();
      article.querySelector('time').dateTime = comment.created_at;

      const repliesElement = article.querySelector('.replies');
      for (const reply of comment.replies) {
        const replyArticle = document.createElement('article');
        replyArticle.className = 'comment';
        replyArticle.textContent = reply.text;
        repliesElement.append(replyArticle);
      }

      const replyButton = article.querySelector('.reply-button');
      replyButton.addEventListener('click', () => {
        if (article.querySelector('.reply-form')) {
          return;
        }

        const form = document.createElement('form');
        form.className = 'reply-form';
        const textarea = document.createElement('textarea');
        textarea.maxLength = 500;
        textarea.required = true;
        const submit = document.createElement('button');
        submit.type = 'submit';
        submit.textContent = 'Enviar respuesta';
        form.append(textarea, submit);

        form.addEventListener('submit', async (event) => {
          event.preventDefault();
          try {
            await api('replies', {
              method: 'POST',
              headers: {'Content-Type': 'application/json'},
              body: JSON.stringify({
                parent_id: comment.id,
                text: textarea.value,
              }),
            });
            await loadComments();
          } catch (error) {
            alert(error.message);
          }
        });

        article.append(form);
      });

      commentsElement.append(article);
    }
  } catch (error) {
    commentsElement.textContent = error.message;
  }
}

commentForm.addEventListener('submit', async (event) => {
  event.preventDefault();
  try {
    await api('comments', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify({text: commentText.value}),
    });
    commentText.value = '';
    characterCount.textContent = '0 / 500';
    await loadComments();
  } catch (error) {
    alert(error.message);
  }
});

loadComments();
