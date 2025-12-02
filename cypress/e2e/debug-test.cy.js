describe('Simple Test - Debug', () => {
  it('should visit login page', () => {
    cy.visit('/login')
    cy.get('body').should('be.visible')
    cy.screenshot('login-page')
  })
  
  it('should visit home without login', () => {
    cy.visit('/')
    cy.screenshot('home-page-no-login')
  })
})
