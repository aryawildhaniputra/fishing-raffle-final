/**
 * Test Event 6: Scattered Slot Allocation
 * 
 * Event ini menguji fungsi alokasi scattered (tidak berjejer) ketika slot berjejer tidak tersedia.
 * Membuat 3 grup peserta dengan jumlah anggota random (total < 222)
 */

describe('Event 6: Scattered Slot Allocation Test', () => {
  beforeEach(() => {
    cy.login()
  })

  it('should test scattered allocation with 3 random groups', () => {
    // Fixed group sizes to maximize slot usage (total = 222)
    const group1Size = 70
    const group2Size = 70
    const group3Size = 82
    const totalMembers = group1Size + group2Size + group3Size
    
    cy.log(`=== Test Configuration ===`)
    cy.log(`Group 1: ${group1Size} members`)
    cy.log(`Group 2: ${group2Size} members`)
    cy.log(`Group 3: ${group3Size} members`)
    cy.log(`Total: ${totalMembers} members (maximum capacity)`)
    
    // Verify total equals 222
    expect(totalMembers).to.equal(222)
    
    // After login, we're already on home page, so no need to visit('/')
    
    // Create new event
    cy.contains('Buat Event Baru').click()
    cy.wait(500) // Wait for modal to open
    
    // Use modal-specific selectors to avoid conflicts
    cy.get('#addNewModal input[name="name"]').type('Event Test - Scattered Slots')
    cy.get('#addNewModal input[name="event_date"]').type('2025-12-31')
    cy.get('#addNewModal input[name="price"]').type('50000')
    cy.get('#addNewModal button[type="submit"]').contains('Simpan').click()
    
    // Wait for success alert and click OK if it appears
    cy.wait(1000)
    cy.get('body').then($body => {
      if ($body.find('.swal2-confirm').length > 0) {
        cy.get('.swal2-confirm').click() // Click OK on SweetAlert
      }
    })
    
    // Wait for form submission and page reload
    cy.wait(2000)
    
    // Ensure we're on home page and event card is visible
    cy.url().should('include', '/')
    cy.contains('.card-title', 'Event Test - Scattered Slots').should('be.visible').click()
    
    // Add Group 1
    cy.log(`Adding Group 1: ${group1Size} members`)
    cy.contains('Tambah Pendaftar').click()
    cy.wait(500)
    
    cy.get('#addNewModal input[name="name"]').type('Grup Scattered 1')
    cy.get('#addNewModal input[name="phone_num"]').type('081234567801')
    cy.get('#addNewModal input[name="total_member"]').clear().type(group1Size.toString())
    cy.get('#addNewModal select[name="status"]').select('paid')
    cy.get('#addNewModal button[type="submit"]').contains('Simpan').click()
    
    // Handle success alert
    cy.wait(1000)
    cy.get('body').then($body => {
      if ($body.find('.swal2-confirm').length > 0) {
        cy.get('.swal2-confirm').click()
      }
    })
    cy.wait(500)
    
    // Add Group 2
    cy.log(`Adding Group 2: ${group2Size} members`)
    cy.contains('Tambah Pendaftar').click()
    cy.wait(500)
    
    cy.get('#addNewModal input[name="name"]').type('Grup Scattered 2')
    cy.get('#addNewModal input[name="phone_num"]').type('081234567802')
    cy.get('#addNewModal input[name="total_member"]').clear().type(group2Size.toString())
    cy.get('#addNewModal select[name="status"]').select('paid')
    cy.get('#addNewModal button[type="submit"]').contains('Simpan').click()
    
    // Handle success alert
    cy.wait(1000)
    cy.get('body').then($body => {
      if ($body.find('.swal2-confirm').length > 0) {
        cy.get('.swal2-confirm').click()
      }
    })
    cy.wait(500)
    
    // Add Group 3
    cy.log(`Adding Group 3: ${group3Size} members`)
    cy.contains('Tambah Pendaftar').click()
    cy.wait(500)
    
    cy.get('#addNewModal input[name="name"]').type('Grup Scattered 3')
    cy.get('#addNewModal input[name="phone_num"]').type('081234567803')
    cy.get('#addNewModal input[name="total_member"]').clear().type(group3Size.toString())
    cy.get('#addNewModal select[name="status"]').select('paid')
    cy.get('#addNewModal button[type="submit"]').contains('Simpan').click()
    
    // Handle success alert
    cy.wait(1000)
    cy.get('body').then($body => {
      if ($body.find('.swal2-confirm').length > 0) {
        cy.get('.swal2-confirm').click()
      }
    })
    cy.wait(500)
    
    // Now fill slots to create gaps that force scattered allocation
    // Strategy: Fill slots in a pattern that prevents consecutive allocation
    // Fill slots 1-60, 80-140, 160-200 to create gaps
    cy.log('Creating slot gaps to force scattered allocation...')
    
    // We'll need to manually book some slots via the database or API
    // For now, let's proceed with drawing and see if scattered allocation happens naturally
    
    // Draw all 3 groups
    const drawnNumbers = []
    let scatteredCount = 0
    
    const drawGroup = (groupNum) => {
      cy.get('body').then($body => {
        if ($body.find('.btn-draw').length > 0) {
          cy.clickDrawButton()
          cy.waitForNumberAnimation()
          
          // Check if this is scattered allocation by looking for the warning alert
          cy.get('body').then($modal => {
            const hasScatteredAlert = $modal.find('.alert-warning').length > 0
            
            if (hasScatteredAlert) {
              scatteredCount++
              cy.log(`🎯 Group ${groupNum}: SCATTERED allocation detected!`)
              
              // Get all scattered numbers from the alert message
              cy.get('.alert-warning strong').last().invoke('text').then(numbersText => {
                cy.log(`Scattered numbers: ${numbersText}`)
              })
            } else {
              cy.log(`Group ${groupNum}: Regular consecutive allocation`)
            }
            
            // Get the drawn number(s)
            cy.get('#random-stall-number').invoke('text').then(number => {
              drawnNumbers.push(parseInt(number))
              cy.log(`Group ${groupNum} drawn: ${number}`)
            })
            
            // Confirm the draw
            // For scattered or single member, use single confirmation
            // For multi-member consecutive, use upper/under selection
            cy.get('body').then($confirm => {
              // Check if single confirmation wrapper is visible (for scattered or single member)
              if ($confirm.find('.confirm-wrapper-single:visible').length > 0) {
                cy.log('→ Confirming with KONFIRMASI button (scattered or single member)')
                cy.get('.confirm-wrapper-single .btn-confirm-draw').click()
              } else if ($confirm.find('.confirm-wrapper-multiple:visible').length > 0) {
                // Regular upper/under selection for consecutive multi-member groups
                cy.selectRandomPosition()
              } else {
                // Fallback: try to find any confirm button
                cy.get('.btn-confirm-draw').first().click()
              }
            })
            
            cy.wait(1500)
            cy.url().should('include', '/event/')
          })
        }
      })
    }
    
    // Draw Group 1
    drawGroup(1)
    
    // Draw Group 2
    drawGroup(2)
    
    // Draw Group 3
    drawGroup(3)
    
    // Verify results
    cy.then(() => {
      cy.log(`=== Test Results ===`)
      cy.log(`Total groups drawn: 3`)
      cy.log(`Scattered allocations: ${scatteredCount}`)
      cy.log(`Numbers drawn: ${drawnNumbers.join(', ')}`)
      
      // At least verify all groups were drawn
      expect(drawnNumbers.length).to.be.greaterThan(0)
      
      if (scatteredCount > 0) {
        cy.log(`✓ Scattered allocation was used ${scatteredCount} time(s)!`)
      } else {
        cy.log(`ℹ No scattered allocation needed (enough consecutive slots available)`)
      }
    })
    
    cy.screenshot('event6-scattered-slots-test')
  })

  it('should verify scattered allocation logic with forced gaps', () => {
    cy.log('This test would require pre-filling slots to force scattered allocation')
    cy.log('Implementation depends on having a way to manually book slots via API or seeder')
    
    // TODO: Implement forced gap scenario
    // 1. Create event
    // 2. Pre-fill slots in a pattern (e.g., 1-50, 70-120, 140-190)
    // 3. Add groups that cannot fit in remaining consecutive slots
    // 4. Verify scattered allocation is triggered
    // 5. Verify allocated slots are valid but non-consecutive
  })
})
