// ***********************************************
// This example commands.js shows you how to
// create various custom commands and overwrite
// existing commands.
//
// For more comprehensive examples of custom
// commands please read more here:
// https://on.cypress.io/custom-commands
// ***********************************************

// Login command
Cypress.Commands.add('login', (username = 'farhan12', password = 'adminpass') => {
  cy.visit('/login')
  cy.get('input[name="username"]').type(username)
  cy.get('input[name="password"]').type(password)
  cy.get('button[type="submit"]').click()
  // After login, redirects to home (/)
  cy.url().should('not.include', '/login')
})

// Wait for modal to be visible
Cypress.Commands.add('waitForModal', (modalId) => {
  cy.get(`#${modalId}`).should('be.visible')
  cy.get(`#${modalId} .modal-dialog`).should('be.visible')
})

// Click draw button and wait for modal
Cypress.Commands.add('clickDrawButton', () => {
  // Klik tab Pengundian
  cy.contains('button', 'Pengundian').click()
  
  // Klik tombol draw
  cy.get('.btn-draw').click()
  cy.waitForModal('drawModal')
})

// Wait for number animation to complete
Cypress.Commands.add('waitForNumberAnimation', () => {
  // Wait for spinner to disappear
  cy.get('#spinner-draw', { timeout: 10000 }).should('not.be.visible')
  
  // Check if error state or content appears
  cy.get('body').then($body => {
    if ($body.find('.not-found-state-draw').is(':visible')) {
      // Error state detected
      cy.log('⚠ Error state detected - closing modal')
      cy.get('.btn-close').first().click()
      return
    }
    
    if ($body.find('#content-wrapper-draw').hasClass('d-none')) {
      // Content not visible, might be error
      cy.log('⚠ Content not visible - closing modal')
      cy.get('.btn-close').first().click()
      return
    }
    
    // Wait for content to appear
    cy.get('#content-wrapper-draw').should('be.visible')
    // Wait a bit for purecounter animation
    cy.wait(500)
  })
})

// Select upper or lower stall
Cypress.Commands.add('selectStallPosition', (position) => {
  if (position === 'upper') {
    cy.get('.btn-upper').click()
  } else if (position === 'lower') {
    cy.get('.btn-under').click()
  } else {
    cy.get('.btn-confirm-draw').first().click()
  }
})

// Random select between upper or lower (if both available)
// Also handles scattered slots and single member groups
Cypress.Commands.add('selectRandomPosition', () => {
  cy.get('body').then($body => {
    // Check if single confirmation wrapper is visible (for scattered or single member)
    if ($body.find('.confirm-wrapper-single:visible').length > 0) {
      cy.log('→ KONFIRMASI (scattered or single member)')
      cy.get('.confirm-wrapper-single .btn-confirm-draw').click()
      
      // Wait for success modal to appear, then handle it
      cy.get('.swal2-container', { timeout: 2000 }).should('be.visible').then(() => {
        cy.get('.swal2-confirm:visible').should('be.visible').then($btn => {
          if ($btn.length > 0) {
            cy.log('→ Clicking OK on success modal')
            cy.get('.swal2-confirm').click()
            // Wait for modal to fully close
            cy.get('.swal2-container').should('not.be.visible')
          }
        })
      })
      return
    }
    
    // Check if multiple confirmation wrapper is visible (for consecutive multi-member)
    if ($body.find('.confirm-wrapper-multiple:visible').length > 0) {
      const upperDisabled = $body.find('.btn-upper').is(':disabled')
      const underDisabled = $body.find('.btn-under').is(':disabled')
      
      if (!upperDisabled && !underDisabled) {
        // Both available, random choice
        const random = Math.random() < 0.5
        if (random) {
          cy.log('🎲 Random: ATAS')
          cy.get('.btn-upper').click()
        } else {
          cy.log('🎲 Random: BAWAH')
          cy.get('.btn-under').click()
        }
      } else if (!upperDisabled) {
        cy.log('→ ATAS (only option)')
        cy.get('.btn-upper').click()
      } else if (!underDisabled) {
        cy.log('→ BAWAH (only option)')
        cy.get('.btn-under').click()
      }
      
      // Wait for success modal to appear, then handle it
      cy.get('.swal2-container', { timeout: 2000 }).should('be.visible').then(() => {
        cy.get('.swal2-confirm:visible').should('be.visible').then($btn => {
          if ($btn.length > 0) {
            cy.log('→ Clicking OK on success modal')
            cy.get('.swal2-confirm').click()
            // Wait for modal to fully close
            cy.get('.swal2-container').should('not.be.visible')
          }
        })
      })
      return
    }
    
    // Fallback: try to find any confirm button
    cy.log('→ Fallback: clicking first confirm button')
    cy.get('.btn-confirm-draw').first().click()
    
    // Wait for success modal to appear, then handle it
    cy.get('.swal2-container', { timeout: 2000 }).should('be.visible').then(() => {
      cy.get('.swal2-confirm:visible').should('be.visible').then($btn => {
        if ($btn.length > 0) {
          cy.log('→ Clicking OK on success modal')
          cy.get('.swal2-confirm').click()
          // Wait for modal to fully close
          cy.get('.swal2-container').should('not.be.visible')
        }
      })
    })
  })
})

// Redraw lottery
Cypress.Commands.add('redrawLottery', () => {
  cy.get('.btn-redraw').click()
  cy.waitForNumberAnimation()
})

// Get total groups not yet drawn from UI
Cypress.Commands.add('getTotalGroupsNotDrawn', () => {
  // Klik tab Pengundian untuk melihat info
  cy.contains('button', 'Pengundian').click()
  
  // Cari text yang menampilkan jumlah grup belum diundi
  // Format: "X Grup Belum Diundi" atau similar
  return cy.get('body').then($body => {
    // Cari elemen yang berisi info jumlah grup
    const text = $body.text()
    
    // Extract number dari text (misal: "111 Grup Belum Diundi")
    const match = text.match(/(\d+)\s*Grup.*Belum/i)
    
    if (match) {
      return parseInt(match[1])
    }
    
    // Fallback: hitung dari jumlah tombol draw yang ada
    const drawButtons = $body.find('.btn-draw')
    return drawButtons.length > 0 ? drawButtons.length : 0
  })
})
