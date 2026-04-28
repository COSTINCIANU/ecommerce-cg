describe('Page Accueil — C.G Boutique', () => {

  beforeEach(() => {
    cy.visit('https://127.0.0.1:8000')
  })

  it('La page accueil se charge correctement', () => {
    cy.get('body').should('exist')
  })

  it('La page accueil retourne un statut 200', () => {
    cy.request('https://127.0.0.1:8000').its('status').should('eq', 200)
  })

  it('Le header est présent', () => {
    cy.get('header').should('exist')
  })

  it('Le footer est présent', () => {
    cy.get('footer').should('exist')
  })

  // it('Le logo C.G est affiché', () => {
  //   cy.get('header').contains('C.G')
  // })
  it('Le logo C.G est affiché', () => {
    cy.get('header').find('img[alt="logo C.G"]').should('exist')
  })
})