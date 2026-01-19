export const initAccordion = (elements, params = {}) => {
  if (!elements) {
    return
  }

  if (!elements?.header && !elements?.content && !elements?.accordion) {
    return
  }

  elements.header.addEventListener('click', (e) => {
    elements.accordion.classList.toggle('active');
  })

  SobolDevTheme.setVariableInPx(elements.content, elements.content.scrollHeight, '--content-height');

  window.addEventListener('resize', (e) => {
    elements.content.style.setProperty('--content-height', 'auto');
    setTimeout(() => {
      SobolDevTheme.setVariableInPx(elements.content, elements.content.scrollHeight, '--content-height');
    }, 0);
  })


  if (params?.open) {
    elements.accordion.classList.add('active');
  }
}